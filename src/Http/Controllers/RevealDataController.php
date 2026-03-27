<?php

namespace Rawand\FilamentReveal\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Rawand\FilamentReveal\Concerns\HasRevealableColumns;
use Rawand\FilamentReveal\Support\RevealTokenGenerator;
use Illuminate\Support\Facades\RateLimiter;
use Rawand\FilamentReveal\Events\ColumnRevealed;
use Rawand\FilamentReveal\Events\ColumnRevealFailed;
use Rawand\FilamentReveal\Events\UnauthorizedRevealAttempt;

/**
 * Controller for securely revealing sensitive column data
 */
class RevealDataController extends Controller
{
    /**
     * Fetch secure column data
     */
    public function fetch(Request $request): JsonResponse
    {
        // Verify authentication
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        // Rate limiting
        $rateLimitConfig = config('filament-reveal.rate_limit');
        if ($rateLimitConfig) {
            $key = 'reveal-column:' . $user->id;
            $maxAttempts = $rateLimitConfig['max_attempts'] ?? 10;
            $decayMinutes = $rateLimitConfig['decay_minutes'] ?? 1;

            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);
                $this->logFailedAttempt($user, 'Rate limit exceeded', $request);
                return $this->errorResponse("Too many requests. Try again in {$seconds} seconds.", 429);
            }
            RateLimiter::hit($key, $decayMinutes * 60);
        }

        // Validate and decode token
        $validated = $this->validateRequest($request);
        $params = RevealTokenGenerator::decode($validated['token']);

        if (!$params) {
            return $this->errorResponse('Invalid or expired token', 400);
        }

        try {
            // Retrieve model
            $model = $this->getModel($params['model'], $params['record_id']);

            if (!$model) {
                return $this->errorResponse('Record not found', 404);
            }

            // Check if model uses HasRevealableColumns trait
            if (!$this->usesRevealableTrait($model)) {
                $this->logFailedAttempt($user, 'Model missing trait', $request, [
                    'model' => get_class($model),
                    'column' => $params['column_name']
                ]);

                if (config('filament-reveal.log_failed_attempts', true)) {
                    \Log::warning('Model does not use HasRevealableColumns trait', [
                        'model' => get_class($model),
                        'column' => $params['column_name']
                    ]);
                }
                return $this->errorResponse('Model does not support column revelation', 403);
            }

            // Check if column is revealable
            if (!$model->isColumnRevealable($params['column_name'])) {
                $this->logFailedAttempt($user, 'Column not in whitelist', $request, [
                    'model' => get_class($model),
                    'column' => $params['column_name'],
                    'revealable_columns' => $model->getRevealableColumns()
                ]);

                if (config('filament-reveal.log_failed_attempts', true)) {
                    \Log::warning('Column is not revealable', [
                        'model' => get_class($model),
                        'column' => $params['column_name'],
                        'revealable_columns' => $model->getRevealableColumns()
                    ]);
                }
                return $this->errorResponse('Column is not revealable', 403);
            }

            // Authorize access
            if (!$model->authorizeRevealColumn($params['column_name'], $user)) {
                // Dispatch security event
                event(new UnauthorizedRevealAttempt(
                    user: $user,
                    modelClass: get_class($model),
                    recordId: $params['record_id'],
                    columnName: $params['column_name'],
                    reason: 'Authorization failed',
                    ipAddress: config('filament-reveal.log_ip_address') ? $request->ip() : null,
                    metadata: ['user_agent' => $request->userAgent()]
                ));

                if (config('filament-reveal.log_failed_attempts', true)) {
                    \Log::warning('Unauthorized column access attempt', [
                        'user_id' => $user->id,
                        'model' => get_class($model),
                        'record_id' => $params['record_id'],
                        'column' => $params['column_name'],
                        'ip' => $request->ip()
                    ]);
                }
                return $this->errorResponse('Unauthorized', 403);
            }

            // Get column value
            $value = $model->getRevealableColumnValue($params['column_name']);

            // Dispatch success event
            event(new ColumnRevealed(
                user: $user,
                modelClass: get_class($model),
                recordId: $params['record_id'],
                columnName: $params['column_name'],
                ipAddress: config('filament-reveal.log_ip_address') ? $request->ip() : null,
                metadata: ['user_agent' => $request->userAgent()]
            ));

            if (config('filament-reveal.audit_logging', true)) {
                \Log::info('Column revealed', [
                    'user_id' => $user->id,
                    'model' => get_class($model),
                    'record_id' => $params['record_id'],
                    'column' => $params['column_name'],
                    'ip' => $request->ip()
                ]);
            }

            return $this->successResponse($value);
        } catch (\Exception $e) {
            \Log::error('Reveal column error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'model' => $validated['model'] ?? null,
                'record_id' => $validated['record_id'] ?? null,
                'column' => $validated['column_name'] ?? null,
            ]);

            return $this->errorResponse('Failed to retrieve data', 500);
        }
    }

    /**
     * Get authenticated user from any guard
     */
    protected function getAuthenticatedUser(): mixed
    {
        foreach (array_keys(config('auth.guards', [])) as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }

        return null;
    }

    /**
     * Validate incoming request
     */
    protected function validateRequest(Request $request): array
    {
        return Validator::make($request->all(), [
            'token' => ['required', 'string'],
        ])->validate();
    }

    /**
     * Get model instance
     */
    protected function getModel(string $modelClass, mixed $recordId): ?Model
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException('Invalid model class');
        }

        if (!is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException('Class is not an Eloquent model');
        }

        return $modelClass::find($recordId);
    }

    /**
     * Check if model uses HasRevealableColumns trait
     */
    protected function usesRevealableTrait(Model $model): bool
    {
        return in_array(HasRevealableColumns::class, class_uses_recursive($model));
    }

    /**
     * Return success response
     */
    protected function successResponse(mixed $value): JsonResponse
    {
        return response()->json([
            'success' => true,
            'value' => $value,
        ]);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Log failed reveal attempt
     */
    protected function logFailedAttempt(mixed $user, string $reason, Request $request, array $context = []): void
    {
        event(new ColumnRevealFailed(
            user: $user,
            modelClass: $context['model'] ?? 'Unknown',
            recordId: $context['record_id'] ?? null,
            columnName: $context['column'] ?? 'Unknown',
            reason: $reason,
            ipAddress: config('filament-reveal.log_ip_address') ? $request->ip() : null,
            metadata: ['user_agent' => $request->userAgent()]
        ));
    }
}
