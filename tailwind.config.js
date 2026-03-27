/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        // Safelist all color variants used by RevealColumn
        // This ensures Tailwind includes these classes in production builds
        'text-primary-600',
        'dark:text-primary-400',
        'text-success-600',
        'dark:text-success-400',
        'text-warning-600',
        'dark:text-warning-400',
        'text-danger-600',
        'dark:text-danger-400',
        'text-info-600',
        'dark:text-info-400',
        'text-gray-600',
        'dark:text-gray-400',
    ],
}
