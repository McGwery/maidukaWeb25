import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    BarChart3,
    CheckCircle2,
    Globe,
    LineChart,
    Lock,
    Package,
    ShoppingBag,
    ShoppingCart,
    Store,
    TrendingUp,
    Users,
    Zap,
} from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    const features = [
        {
            icon: Store,
            title: 'Multi-Location Management',
            description:
                'Seamlessly manage multiple shop locations from a single dashboard with real-time synchronization.',
        },
        {
            icon: Package,
            title: 'Smart Inventory Control',
            description:
                'Track stock levels across all locations, get low-stock alerts, and automate reordering processes.',
        },
        {
            icon: BarChart3,
            title: 'Advanced Analytics',
            description:
                'Gain deep insights with comprehensive reports, sales trends, and performance metrics.',
        },
        {
            icon: Users,
            title: 'Team Collaboration',
            description:
                'Assign roles, manage permissions, and coordinate your team across multiple locations.',
        },
        {
            icon: ShoppingCart,
            title: 'Unified POS System',
            description:
                'Process sales quickly with our intuitive point-of-sale system, online or offline.',
        },
        {
            icon: TrendingUp,
            title: 'Growth Insights',
            description:
                'Identify opportunities, optimize operations, and scale your business with data-driven decisions.',
        },
    ];

    const benefits = [
        {
            stat: '70%',
            label: 'Time Saved',
            description: 'on administrative tasks',
        },
        {
            stat: '3x',
            label: 'Faster',
            description: 'inventory turnover',
        },
        {
            stat: '50%',
            label: 'Reduced',
            description: 'operational costs',
        },
        {
            stat: '99.9%',
            label: 'Uptime',
            description: 'reliability guarantee',
        },
    ];

    const plans = [
        {
            name: 'Free',
            price: '0',
            duration: '/year',
            description: 'Perfect for getting started',
            features: [
                'Basic inventory management',
                'Up to 50 products',
                'Offline mode only',
                'Single user',
                'Basic reports',
            ],
            cta: 'Start Free',
            popular: false,
        },
        {
            name: 'Basic',
            price: '9.99',
            duration: '/month',
            description: 'For growing businesses',
            features: [
                'Advanced inventory management',
                'Up to 500 products',
                'Online & Offline mode',
                'Up to 3 users',
                'Customer management',
                'Email support',
            ],
            cta: 'Get Started',
            popular: false,
        },
        {
            name: 'Premium',
            price: '12,000',
            duration: '/month',
            description: 'For established retailers',
            features: [
                'Unlimited products',
                'Both online & offline mode',
                'Up to 10 users',
                'Advanced reports & analytics',
                'Multi-location support',
                'Priority support',
                'API access',
            ],
            cta: 'Start Premium',
            popular: true,
        },
        {
            name: 'Enterprise',
            price: '99.99',
            duration: '/month',
            description: 'For large-scale operations',
            features: [
                'Everything in Premium',
                'Unlimited users',
                'Custom integrations',
                'Dedicated support',
                'Custom features',
                'Training & onboarding',
                'SLA guarantee',
            ],
            cta: 'Contact Sales',
            popular: false,
        },
    ];

    const useCases = [
        {
            type: 'Retail Chains',
            description:
                'Manage multiple retail stores with centralized inventory and sales tracking.',
        },
        {
            type: 'Restaurants & Cafes',
            description:
                'Handle multiple outlets with menu management and kitchen coordination.',
        },
        {
            type: 'Pharmacies',
            description:
                'Track medications, expiry dates, and compliance across all branches.',
        },
        {
            type: 'Wholesale Distributors',
            description:
                'Manage bulk orders, supplier relationships, and distribution networks.',
        },
        {
            type: 'E-commerce Sellers',
            description:
                'Sync online and offline inventory for omnichannel retail operations.',
        },
        {
            type: 'Fashion Boutiques',
            description:
                'Manage seasonal collections, sizes, and variants across locations.',
        },
    ];

    const techStack = [
        { name: 'Laravel', description: 'Robust PHP backend' },
        { name: 'React', description: 'Modern UI framework' },
        { name: 'Inertia.js', description: 'Seamless SPA experience' },
        { name: 'MySQL', description: 'Reliable database' },
        { name: 'Pusher', description: 'Real-time updates' },
        { name: 'Tailwind CSS', description: 'Beautiful design' },
    ];

    return (
        <>
            <Head title="MaiDuka - Manage Multiple Shops, One Platform" />

            {/* Navigation */}
            <nav className="fixed top-0 z-50 w-full border-b border-emerald-100 bg-white/80 backdrop-blur-md dark:border-emerald-900/30 dark:bg-gray-900/80">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <div className="flex items-center space-x-2">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600">
                            <Store className="h-6 w-6 text-white" />
                        </div>
                        <span className="text-2xl font-bold text-gray-900 dark:text-white">
                            MaiDuka
                        </span>
                    </div>

                    <div className="hidden items-center space-x-8 md:flex">
                        <a
                            href="#features"
                            className="text-sm font-medium text-gray-600 transition-colors hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400"
                        >
                            Features
                        </a>
                        <a
                            href="#benefits"
                            className="text-sm font-medium text-gray-600 transition-colors hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400"
                        >
                            Benefits
                        </a>
                        <a
                            href="#pricing"
                            className="text-sm font-medium text-gray-600 transition-colors hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400"
                        >
                            Pricing
                        </a>
                        <a
                            href="#use-cases"
                            className="text-sm font-medium text-gray-600 transition-colors hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400"
                        >
                            Use Cases
                        </a>
                    </div>

                    <div className="flex items-center space-x-4">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition-all hover:bg-emerald-700 hover:shadow-emerald-600/40"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="text-sm font-medium text-gray-600 transition-colors hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition-all hover:bg-emerald-700 hover:shadow-emerald-600/40"
                                >
                                    Get Started
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </nav>

            {/* Hero Section */}
            <section className="relative overflow-hidden bg-gradient-to-b from-emerald-50 via-white to-white pt-32 pb-20 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800">
                <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNnoiIHN0cm9rZT0icmdiYSgxNiwgMTg1LCAxMjksIDAuMDUpIi8+PC9nPjwvc3ZnPg==')] opacity-40" />

                <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <div className="mb-6 inline-flex items-center space-x-2 rounded-full bg-emerald-100 px-4 py-2 dark:bg-emerald-900/30">
                            <Zap className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            <span className="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                Trusted by 1,000+ Retailers
                            </span>
                        </div>

                        <h1 className="mb-6 text-5xl font-extrabold tracking-tight text-gray-900 sm:text-6xl lg:text-7xl dark:text-white">
                            Manage Multiple Shops,
                            <span className="block bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                                One Platform
                            </span>
                        </h1>

                        <p className="mx-auto mb-10 max-w-3xl text-lg leading-relaxed text-gray-600 sm:text-xl dark:text-gray-300">
                            MaiDuka is the revolutionary multi-shop management
                            system that streamlines operations for retailers
                            managing multiple locations. Simplify inventory,
                            sales, and team coordination—all from one intuitive
                            dashboard.
                        </p>

                        <div className="flex flex-col items-center justify-center space-y-4 sm:flex-row sm:space-x-4 sm:space-y-0">
                            <Link
                                href={register()}
                                className="group flex w-full items-center justify-center space-x-2 rounded-lg bg-emerald-600 px-8 py-4 text-base font-semibold text-white shadow-xl shadow-emerald-600/30 transition-all hover:bg-emerald-700 hover:shadow-emerald-600/50 sm:w-auto"
                            >
                                <span>Start Free Trial</span>
                                <svg
                                    className="h-5 w-5 transition-transform group-hover:translate-x-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M13 7l5 5m0 0l-5 5m5-5H6"
                                    />
                                </svg>
                            </Link>
                            <a
                                href="#demo"
                                className="flex w-full items-center justify-center space-x-2 rounded-lg border-2 border-emerald-600 bg-transparent px-8 py-4 text-base font-semibold text-emerald-600 transition-all hover:bg-emerald-50 sm:w-auto dark:border-emerald-400 dark:text-emerald-400 dark:hover:bg-emerald-900/20"
                            >
                                <span>Request Demo</span>
                            </a>
                        </div>

                        <p className="mt-6 text-sm text-gray-500 dark:text-gray-400">
                            No credit card required • Free 30-day trial •
                            Cancel anytime
                        </p>
                    </div>

                    {/* Hero Image/Mockup */}
                    <div className="mt-20">
                        <div className="relative mx-auto max-w-5xl rounded-2xl border border-emerald-200 bg-white p-4 shadow-2xl shadow-emerald-600/10 dark:border-emerald-800 dark:bg-gray-800">
                            <div className="aspect-video rounded-lg bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900/30 dark:to-teal-900/30">
                                <div className="flex h-full items-center justify-center">
                                    <div className="text-center">
                                        <ShoppingBag className="mx-auto mb-4 h-24 w-24 text-emerald-600 dark:text-emerald-400" />
                                        <p className="text-lg font-medium text-gray-600 dark:text-gray-300">
                                            Dashboard Preview
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Problem Statement */}
            <section className="bg-white py-20 dark:bg-gray-800">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <h2 className="mb-4 text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                            The Challenge of Multi-Shop Management
                        </h2>
                        <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                            Running multiple retail locations comes with
                            complex challenges that traditional systems can't
                            handle efficiently.
                        </p>
                    </div>

                    <div className="mt-16 grid gap-8 md:grid-cols-3">
                        <div className="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900/30 dark:bg-red-900/10">
                            <div className="mb-4 inline-flex rounded-lg bg-red-100 p-3 dark:bg-red-900/30">
                                <svg
                                    className="h-6 w-6 text-red-600 dark:text-red-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                Inventory Chaos
                            </h3>
                            <p className="text-gray-600 dark:text-gray-300">
                                Tracking stock across locations is
                                time-consuming and error-prone, leading to
                                stockouts and overstocking.
                            </p>
                        </div>

                        <div className="rounded-xl border border-orange-200 bg-orange-50 p-6 dark:border-orange-900/30 dark:bg-orange-900/10">
                            <div className="mb-4 inline-flex rounded-lg bg-orange-100 p-3 dark:bg-orange-900/30">
                                <svg
                                    className="h-6 w-6 text-orange-600 dark:text-orange-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                Manual Processes
                            </h3>
                            <p className="text-gray-600 dark:text-gray-300">
                                Hours wasted on spreadsheets, phone calls, and
                                paperwork instead of growing your business.
                            </p>
                        </div>

                        <div className="rounded-xl border border-yellow-200 bg-yellow-50 p-6 dark:border-yellow-900/30 dark:bg-yellow-900/10">
                            <div className="mb-4 inline-flex rounded-lg bg-yellow-100 p-3 dark:bg-yellow-900/30">
                                <svg
                                    className="h-6 w-6 text-yellow-600 dark:text-yellow-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth={2}
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                            </div>
                            <h3 className="mb-2 text-xl font-semibold text-gray-900 dark:text-white">
                                Limited Visibility
                            </h3>
                            <p className="text-gray-600 dark:text-gray-300">
                                No unified view of performance across
                                locations, making strategic decisions difficult.
                            </p>
                        </div>
                    </div>

                    <div className="mt-12 text-center">
                        <div className="inline-flex items-center space-x-2 rounded-full bg-emerald-100 px-6 py-3 dark:bg-emerald-900/30">
                            <CheckCircle2 className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            <span className="font-semibold text-emerald-700 dark:text-emerald-300">
                                MaiDuka solves all of these problems
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section
                id="features"
                className="bg-gradient-to-b from-white to-emerald-50 py-20 dark:from-gray-800 dark:to-gray-900"
            >
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <h2 className="mb-4 text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                            Powerful Features for Modern Retailers
                        </h2>
                        <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                            Everything you need to manage your multi-location
                            retail operations efficiently and profitably.
                        </p>
                    </div>

                    <div className="mt-16 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                        {features.map((feature, index) => (
                            <div
                                key={index}
                                className="group rounded-xl border border-emerald-100 bg-white p-8 transition-all hover:border-emerald-300 hover:shadow-xl hover:shadow-emerald-600/10 dark:border-emerald-900/30 dark:bg-gray-800 dark:hover:border-emerald-700"
                            >
                                <div className="mb-5 inline-flex rounded-lg bg-emerald-100 p-3 transition-colors group-hover:bg-emerald-200 dark:bg-emerald-900/30 dark:group-hover:bg-emerald-800/50">
                                    <feature.icon className="h-7 w-7 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <h3 className="mb-3 text-xl font-semibold text-gray-900 dark:text-white">
                                    {feature.title}
                                </h3>
                                <p className="text-gray-600 dark:text-gray-300">
                                    {feature.description}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Benefits/Stats Section */}
            <section
                id="benefits"
                className="bg-gradient-to-br from-emerald-600 to-teal-600 py-20 dark:from-emerald-700 dark:to-teal-700"
            >
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-16 text-center">
                        <h2 className="mb-4 text-3xl font-bold text-white sm:text-4xl">
                            Real Results for Real Businesses
                        </h2>
                        <p className="mx-auto max-w-3xl text-lg text-emerald-100">
                            Join thousands of retailers who have transformed
                            their operations with MaiDuka.
                        </p>
                    </div>

                    <div className="grid gap-8 md:grid-cols-4">
                        {benefits.map((benefit, index) => (
                            <div
                                key={index}
                                className="text-center"
                            >
                                <div className="mb-3 text-5xl font-extrabold text-white">
                                    {benefit.stat}
                                </div>
                                <div className="mb-1 text-xl font-semibold text-emerald-100">
                                    {benefit.label}
                                </div>
                                <div className="text-emerald-200">
                                    {benefit.description}
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-16 text-center">
                        <Link
                            href={register()}
                            className="inline-flex items-center space-x-2 rounded-lg bg-white px-8 py-4 text-base font-semibold text-emerald-600 shadow-xl transition-all hover:bg-emerald-50"
                        >
                            <span>Start Your Success Story</span>
                            <svg
                                className="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>
            </section>

            {/* Use Cases Section */}
            <section
                id="use-cases"
                className="bg-white py-20 dark:bg-gray-800"
            >
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <h2 className="mb-4 text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                            Built for Every Type of Retailer
                        </h2>
                        <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                            From cafes to pharmacies, MaiDuka adapts to your
                            unique business needs.
                        </p>
                    </div>

                    <div className="mt-16 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {useCases.map((useCase, index) => (
                            <div
                                key={index}
                                className="rounded-xl border border-gray-200 bg-gray-50 p-6 transition-all hover:border-emerald-300 hover:shadow-lg dark:border-gray-700 dark:bg-gray-900 dark:hover:border-emerald-700"
                            >
                                <div className="mb-3 flex items-center space-x-2">
                                    <Globe className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                        {useCase.type}
                                    </h3>
                                </div>
                                <p className="text-gray-600 dark:text-gray-300">
                                    {useCase.description}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Pricing Section */}
            <section
                id="pricing"
                className="bg-gradient-to-b from-white to-emerald-50 py-20 dark:from-gray-800 dark:to-gray-900"
            >
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <h2 className="mb-4 text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                            Choose Your Perfect Plan
                        </h2>
                        <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                            Flexible pricing that grows with your business. No
                            hidden fees.
                        </p>
                    </div>

                    <div className="mt-16 grid gap-8 lg:grid-cols-4">
                        {plans.map((plan, index) => (
                            <div
                                key={index}
                                className={`relative rounded-2xl border p-8 transition-all ${
                                    plan.popular
                                        ? 'border-emerald-500 bg-emerald-50 shadow-xl shadow-emerald-600/20 dark:border-emerald-400 dark:bg-emerald-900/20'
                                        : 'border-gray-200 bg-white hover:border-emerald-300 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:hover:border-emerald-700'
                                }`}
                            >
                                {plan.popular && (
                                    <div className="absolute -top-4 left-1/2 -translate-x-1/2">
                                        <span className="inline-flex rounded-full bg-emerald-600 px-4 py-1 text-sm font-semibold text-white">
                                            Most Popular
                                        </span>
                                    </div>
                                )}

                                <div className="mb-6">
                                    <h3 className="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
                                        {plan.name}
                                    </h3>
                                    <p className="text-sm text-gray-600 dark:text-gray-300">
                                        {plan.description}
                                    </p>
                                </div>

                                <div className="mb-6">
                                    <div className="flex items-baseline">
                                        <span className="text-4xl font-extrabold text-gray-900 dark:text-white">
                                            {plan.price.includes(',')
                                                ? plan.price
                                                : `$${plan.price}`}
                                        </span>
                                        <span className="ml-2 text-gray-600 dark:text-gray-300">
                                            {plan.duration}
                                        </span>
                                    </div>
                                </div>

                                <ul className="mb-8 space-y-3">
                                    {plan.features.map((feature, fIndex) => (
                                        <li
                                            key={fIndex}
                                            className="flex items-start space-x-3"
                                        >
                                            <CheckCircle2 className="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" />
                                            <span className="text-gray-600 dark:text-gray-300">
                                                {feature}
                                            </span>
                                        </li>
                                    ))}
                                </ul>

                                <Link
                                    href={register()}
                                    className={`block w-full rounded-lg py-3 text-center font-semibold transition-all ${
                                        plan.popular
                                            ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/30 hover:bg-emerald-700'
                                            : 'border-2 border-emerald-600 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-400 dark:text-emerald-400 dark:hover:bg-emerald-900/20'
                                    }`}
                                >
                                    {plan.cta}
                                </Link>
                            </div>
                        ))}
                    </div>

                    <div className="mt-12 text-center">
                        <p className="text-gray-600 dark:text-gray-300">
                            All plans include 30-day free trial. Need a custom
                            solution?{' '}
                            <a
                                href="#contact"
                                className="font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300"
                            >
                                Contact our sales team
                            </a>
                        </p>
                    </div>
                </div>
            </section>

            {/* Tech Stack Section */}
            <section className="bg-white py-20 dark:bg-gray-800">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="text-center">
                        <div className="mb-4 inline-flex items-center space-x-2 rounded-full bg-emerald-100 px-4 py-2 dark:bg-emerald-900/30">
                            <Lock className="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            <span className="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                                Enterprise-Grade Technology
                            </span>
                        </div>
                        <h2 className="mb-4 text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">
                            Built on Modern, Reliable Technology
                        </h2>
                        <p className="mx-auto max-w-3xl text-lg text-gray-600 dark:text-gray-300">
                            MaiDuka is powered by industry-leading technologies
                            to ensure security, performance, and scalability.
                        </p>
                    </div>

                    <div className="mt-12 grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-6">
                        {techStack.map((tech, index) => (
                            <div
                                key={index}
                                className="rounded-xl border border-gray-200 bg-gray-50 p-6 text-center transition-all hover:border-emerald-300 hover:shadow-lg dark:border-gray-700 dark:bg-gray-900 dark:hover:border-emerald-700"
                            >
                                <div className="mb-2 text-lg font-bold text-gray-900 dark:text-white">
                                    {tech.name}
                                </div>
                                <div className="text-sm text-gray-600 dark:text-gray-300">
                                    {tech.description}
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-12 rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-8 dark:border-emerald-800 dark:from-emerald-900/20 dark:to-teal-900/20">
                        <div className="grid gap-6 md:grid-cols-3">
                            <div className="text-center">
                                <Lock className="mx-auto mb-3 h-8 w-8 text-emerald-600 dark:text-emerald-400" />
                                <h3 className="mb-2 font-semibold text-gray-900 dark:text-white">
                                    Bank-Level Security
                                </h3>
                                <p className="text-sm text-gray-600 dark:text-gray-300">
                                    SSL encryption & secure data centers
                                </p>
                            </div>
                            <div className="text-center">
                                <LineChart className="mx-auto mb-3 h-8 w-8 text-emerald-600 dark:text-emerald-400" />
                                <h3 className="mb-2 font-semibold text-gray-900 dark:text-white">
                                    99.9% Uptime
                                </h3>
                                <p className="text-sm text-gray-600 dark:text-gray-300">
                                    Reliable infrastructure with redundancy
                                </p>
                            </div>
                            <div className="text-center">
                                <Zap className="mx-auto mb-3 h-8 w-8 text-emerald-600 dark:text-emerald-400" />
                                <h3 className="mb-2 font-semibold text-gray-900 dark:text-white">
                                    Lightning Fast
                                </h3>
                                <p className="text-sm text-gray-600 dark:text-gray-300">
                                    Optimized for speed and performance
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Final CTA Section */}
            <section className="bg-gradient-to-br from-emerald-600 to-teal-600 py-20 dark:from-emerald-700 dark:to-teal-700">
                <div className="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                    <h2 className="mb-6 text-4xl font-extrabold text-white sm:text-5xl">
                        Ready to Transform Your Retail Operations?
                    </h2>
                    <p className="mb-10 text-xl text-emerald-100">
                        Join thousands of retailers who trust MaiDuka to manage
                        their multi-location businesses. Start your free trial
                        today—no credit card required.
                    </p>

                    <div className="flex flex-col items-center justify-center space-y-4 sm:flex-row sm:space-x-4 sm:space-y-0">
                        <Link
                            href={register()}
                            className="flex w-full items-center justify-center space-x-2 rounded-lg bg-white px-8 py-4 text-lg font-semibold text-emerald-600 shadow-xl transition-all hover:bg-emerald-50 sm:w-auto"
                        >
                            <span>Start Free Trial</span>
                            <svg
                                className="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                />
                            </svg>
                        </Link>
                        <a
                            href="#contact"
                            className="flex w-full items-center justify-center rounded-lg border-2 border-white px-8 py-4 text-lg font-semibold text-white transition-all hover:bg-white/10 sm:w-auto"
                        >
                            Schedule a Demo
                        </a>
                    </div>

                    <p className="mt-6 text-emerald-100">
                        Questions? Call us at +255 123 456 789 or email
                        support@maiduka.com
                    </p>
                </div>
            </section>

            {/* Footer */}
            <footer className="border-t border-gray-200 bg-white py-12 dark:border-gray-700 dark:bg-gray-900">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="grid gap-8 md:grid-cols-4">
                        <div className="md:col-span-1">
                            <div className="mb-4 flex items-center space-x-2">
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600">
                                    <Store className="h-6 w-6 text-white" />
                                </div>
                                <span className="text-2xl font-bold text-gray-900 dark:text-white">
                                    MaiDuka
                                </span>
                            </div>
                            <p className="text-sm text-gray-600 dark:text-gray-300">
                                The complete solution for managing multiple
                                retail locations efficiently.
                            </p>
                        </div>

                        <div>
                            <h3 className="mb-4 font-semibold text-gray-900 dark:text-white">
                                Product
                            </h3>
                            <ul className="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                <li>
                                    <a
                                        href="#features"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Features
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#pricing"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Pricing
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#use-cases"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Use Cases
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        API Docs
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 className="mb-4 font-semibold text-gray-900 dark:text-white">
                                Company
                            </h3>
                            <ul className="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                <li>
                                    <a
                                        href="#"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        About Us
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Blog
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Careers
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Contact
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 className="mb-4 font-semibold text-gray-900 dark:text-white">
                                Legal
                            </h3>
                            <ul className="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                <li>
                                    <a
                                        href="#"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Privacy Policy
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Terms of Service
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Cookie Policy
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        className="hover:text-emerald-600 dark:hover:text-emerald-400"
                                    >
                                        Security
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div className="mt-12 border-t border-gray-200 pt-8 dark:border-gray-700">
                        <div className="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0">
                            <p className="text-sm text-gray-600 dark:text-gray-300">
                                © 2025 MaiDuka. All rights reserved.
                            </p>
                            <div className="flex space-x-6">
                                <a
                                    href="#"
                                    className="text-gray-600 transition-colors hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400"
                                >
                                    <span className="sr-only">Twitter</span>
                                    <svg
                                        className="h-6 w-6"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                    </svg>
                                </a>
                                <a
                                    href="#"
                                    className="text-gray-600 transition-colors hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400"
                                >
                                    <span className="sr-only">Facebook</span>
                                    <svg
                                        className="h-6 w-6"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                </a>
                                <a
                                    href="#"
                                    className="text-gray-600 transition-colors hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400"
                                >
                                    <span className="sr-only">LinkedIn</span>
                                    <svg
                                        className="h-6 w-6"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </>
    );
}

