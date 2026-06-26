<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'Track your recurring subscriptions, monitor renewals, and stay on top of your expenses.', ENT_QUOTES) ?>" />
    <title><?= htmlspecialchars($pageTitle ?? 'SubsTrack', ENT_QUOTES) ?> | SubsTrack</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#f0f4ff',
                            100: '#e0eaff',
                            300: '#93b4fd',
                            400: '#60a5fa',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        surface: {
                            900: '#0a0a0f',
                            800: '#111118',
                            700: '#1a1a27',
                            600: '#22223b',
                            500: '#2d2d4a',
                        }
                    },
                    backgroundImage: {
                        'gradient-brand': 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%)',
                        'gradient-dark':  'linear-gradient(180deg, #0a0a0f 0%, #111118 100%)',
                    },
                    boxShadow: {
                        'glow':    '0 0 20px rgba(99,102,241,0.35)',
                        'glow-lg': '0 0 40px rgba(99,102,241,0.25)',
                        'card':    '0 4px 24px rgba(0,0,0,0.4)',
                    },
                    animation: {
                        'fade-in':    'fadeIn 0.4s ease-out',
                        'slide-up':   'slideUp 0.4s ease-out',
                        'pulse-glow': 'pulseGlow 2s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn:    { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp:   { '0%': { opacity: '0', transform: 'translateY(16px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        pulseGlow: { '0%,100%': { boxShadow: '0 0 10px rgba(99,102,241,0.3)' }, '50%': { boxShadow: '0 0 25px rgba(99,102,241,0.7)' } },
                    }
                }
            }
        }
    </script>

    <!-- App Styles -->
    <link rel="stylesheet" href="/css/style.css" />
</head>
<body class="bg-surface-900 text-gray-100 font-sans antialiased min-h-screen flex flex-col">
