<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>VTubeID API</title>

    <link
        rel="icon"
        type="image/svg+xml"
        href="/images/favicon.svg"
    >

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Nunito Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: '#A555EC',
                        secondary: '#D09CFA',
                        soft: '#F3CCFF',
                        cream: '#FFFFD0',
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >
</head>

<body class="min-h-screen bg-[#FFFFD0] text-gray-800 font-sans">

    <!-- Background -->
    <div class="fixed inset-0 -z-10 overflow-hidden">

        <div
            class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-[#F3CCFF]/70 blur-3xl"
        ></div>

        <div
            class="absolute -right-32 top-20 h-96 w-96 rounded-full bg-[#D09CFA]/40 blur-3xl"
        ></div>

        <div
            class="absolute bottom-0 left-1/2 h-80 w-80 -translate-x-1/2 rounded-full bg-[#F3CCFF]/50 blur-3xl"
        ></div>

    </div>


    <!-- Navbar -->
    <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">

        <div class="flex items-center gap-3">

            <img
                src="/images/favicon.svg" alt="VTubeID API"
                class="flex h-10 w-10 items-center bg-cover justify-center shadow-lg shadow-[#A555EC]/20"
            >

            <div>
                <p class="font-extrabold leading-none">
                    VTubeID API
                </p>

                <p class="mt-1 text-xs text-gray-500">
                    Backend Service
                </p>
            </div>

        </div>

        <div
            class="hidden rounded-full border border-[#D09CFA]/40 bg-white/50 px-4 py-2 text-sm font-semibold text-gray-600 backdrop-blur-sm sm:block"
        >
            v1.0
        </div>

    </nav>


    <!-- Hero -->
    <main class="mx-auto max-w-6xl px-6 pb-20 pt-16">

        <section class="text-center">

            <!-- Status -->
            <div
                class="mx-auto mb-7 inline-flex items-center gap-2 rounded-full border border-[#D09CFA]/40 bg-white/60 px-4 py-2 text-sm font-semibold shadow-sm backdrop-blur"
            >

                <span class="relative flex h-2.5 w-2.5">
                    <span
                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"
                    ></span>

                    <span
                        class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-500"
                    ></span>
                </span>

                API Operational

            </div>


            <!-- Heading -->
            <h1
                class="mx-auto max-w-3xl text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl"
            >
                VTubeID
                <span class="text-[#A555EC]">
                    API
                </span>
            </h1>


            <p
                class="mx-auto mt-6 max-w-2xl text-base leading-7 text-gray-600 sm:text-lg"
            >
                API untuk menyediakan data VTuber Indonesia,
                organisasi, sosial media, dan informasi terkait
                dalam satu layanan.
            </p>


            <!-- API URL -->
            <div
                class="mx-auto mt-8 flex max-w-xl items-center justify-between rounded-2xl border border-[#D09CFA]/40 bg-white/70 p-2 pl-5 shadow-lg shadow-[#A555EC]/5 backdrop-blur"
            >

                <code class="overflow-hidden text-left text-sm font-semibold text-gray-600">
                    /api
                </code>

                <span
                    class="rounded-xl bg-[#A555EC] px-4 py-2 text-sm font-bold text-white"
                >
                    REST API
                </span>

            </div>

        </section>


        <!-- Info Cards -->
        <section class="mt-16 grid gap-5 sm:grid-cols-3">

            <!-- Status -->
            <div
                class="rounded-3xl border border-[#D09CFA]/30 bg-white/70 p-6 shadow-lg shadow-[#A555EC]/5 backdrop-blur transition hover:-translate-y-1"
            >

                <div
                    class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-green-100 text-green-600"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>

                <p class="text-sm font-semibold text-gray-500">
                    Status
                </p>

                <p class="mt-1 text-xl font-extrabold">
                    Operational
                </p>

            </div>


            <!-- Version -->
            <div
                class="rounded-3xl border border-[#D09CFA]/30 bg-white/70 p-6 shadow-lg shadow-[#A555EC]/5 backdrop-blur transition hover:-translate-y-1"
            >

                <div
                    class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-[#F3CCFF] text-[#A555EC]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                </div>

                <p class="text-sm font-semibold text-gray-500">
                    Version
                </p>

                <p class="mt-1 text-xl font-extrabold">
                    v1.0
                </p>

            </div>


            <!-- Framework -->
            <div
                class="rounded-3xl border border-[#D09CFA]/30 bg-white/70 p-6 shadow-lg shadow-[#A555EC]/5 backdrop-blur transition hover:-translate-y-1"
            >

                <div
                    class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-[#FFFFD0] text-[#A555EC]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M13 10V3L4 14h7v7l9-11h-7z"
                        />
                    </svg>
                </div>

                <p class="text-sm font-semibold text-gray-500">
                    Framework
                </p>

                <p class="mt-1 text-xl font-extrabold">
                    Laravel 13
                </p>

            </div>

        </section>


        <!-- Endpoints -->
        <section class="mt-10">

            <div
                class="rounded-3xl border border-[#D09CFA]/30 bg-white/70 p-6 shadow-lg shadow-[#A555EC]/5 backdrop-blur sm:p-8"
            >

                <div class="mb-6">

                    <p class="text-sm font-bold uppercase tracking-wider text-[#A555EC]">
                        API Overview
                    </p>

                    <h2 class="mt-1 text-2xl font-extrabold">
                        Available Resources
                    </h2>

                </div>


                <div class="grid gap-3 sm:grid-cols-2">

                    <div
                        class="flex items-center justify-between rounded-2xl bg-[#FFFFD0]/70 px-5 py-4"
                    >
                        <div class="flex items-center gap-3">

                            <span
                                class="rounded-lg bg-green-100 px-2.5 py-1 text-xs font-extrabold text-green-700"
                            >
                                GET
                            </span>

                            <span class="font-semibold">
                                /api/vtubers
                            </span>

                        </div>

                        <span class="text-xs text-gray-400">
                            VTubers
                        </span>
                    </div>


                    <div
                        class="flex items-center justify-between rounded-2xl bg-[#FFFFD0]/70 px-5 py-4"
                    >
                        <div class="flex items-center gap-3">

                            <span
                                class="rounded-lg bg-green-100 px-2.5 py-1 text-xs font-extrabold text-green-700"
                            >
                                GET
                            </span>

                            <span class="font-semibold">
                                /api/organizations
                            </span>

                        </div>

                        <span class="text-xs text-gray-400">
                            Organizations
                        </span>
                    </div>


                    <div
                        class="flex items-center justify-between rounded-2xl bg-[#FFFFD0]/70 px-5 py-4"
                    >
                        <div class="flex items-center gap-3">

                            <span
                                class="rounded-lg bg-green-100 px-2.5 py-1 text-xs font-extrabold text-green-700"
                            >
                                GET
                            </span>

                            <span class="font-semibold">
                                /api/tags
                            </span>

                        </div>

                        <span class="text-xs text-gray-400">
                            Tags
                        </span>
                    </div>


                    <div
                        class="flex items-center justify-between rounded-2xl bg-[#FFFFD0]/70 px-5 py-4"
                    >
                        <div class="flex items-center gap-3">

                            <span
                                class="rounded-lg bg-[#F3CCFF] px-2.5 py-1 text-xs font-extrabold text-[#A555EC]"
                            >
                                AUTH
                            </span>

                            <span class="font-semibold">
                                /api/login
                            </span>

                        </div>

                        <span class="text-xs text-gray-400">
                            Authentication
                        </span>
                    </div>

                </div>

            </div>

        </section>


        <!-- Footer -->
        <footer class="mt-12 text-center">

            <p class="text-sm text-gray-500">
                VTubeID API
                <span class="mx-1">•</span>
                Powered by Laravel 13
            </p>

            <p class="mt-2 text-xs text-gray-400">
                © {{ date('Y') }} VTubeID API. All rights reserved.
            </p>

        </footer>

    </main>

</body>
</html>