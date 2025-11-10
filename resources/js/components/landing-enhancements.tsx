// Smooth Scroll Enhancement for MaiDuka Landing Page
// Add this to your welcome.tsx or create a separate hooks file

import { useEffect, useState } from 'react';
import { Menu, X } from 'lucide-react';

/**
 * Custom hook to enable smooth scrolling for anchor links
 * Usage: Add this hook to your Welcome component
 */
export function useSmoothScroll() {
    useEffect(() => {
        // Handle smooth scrolling for anchor links
        const handleClick = (e: MouseEvent) => {
            const target = e.target as HTMLElement;
            const anchor = target.closest('a[href^="#"]');

            if (anchor) {
                const href = anchor.getAttribute('href');
                if (href && href.startsWith('#')) {
                    e.preventDefault();
                    const element = document.querySelector(href);
                    if (element) {
                        element.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start',
                        });
                    }
                }
            }
        };

        document.addEventListener('click', handleClick);
        return () => document.removeEventListener('click', handleClick);
    }, []);
}

/**
 * Scroll to top button component
 * Add this to your landing page for better UX
 */
export function ScrollToTop() {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const toggleVisibility = () => {
            if (window.pageYOffset > 300) {
                setIsVisible(true);
            } else {
                setIsVisible(false);
            }
        };

        window.addEventListener('scroll', toggleVisibility);
        return () => window.removeEventListener('scroll', toggleVisibility);
    }, []);

    const scrollToTop = () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    };

    return (
        <>
            {isVisible && (
                <button
                    onClick={scrollToTop}
                    className="fixed bottom-8 right-8 z-50 rounded-full bg-emerald-600 p-3 text-white shadow-lg transition-all hover:bg-emerald-700 hover:shadow-xl"
                    aria-label="Scroll to top"
                >
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
                            d="M5 10l7-7m0 0l7 7m-7-7v18"
                        />
                    </svg>
                </button>
            )}
        </>
    );
}

/**
 * Fade-in on scroll animation hook
 * Add this to animate sections as they come into view
 */
export function useFadeInOnScroll() {
    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            },
            {
                threshold: 0.1,
                rootMargin: '0px 0px -100px 0px',
            }
        );

        // Observe all sections
        const sections = document.querySelectorAll('section');
        sections.forEach((section) => observer.observe(section));

        return () => {
            sections.forEach((section) => observer.unobserve(section));
        };
    }, []);
}

/**
 * Add these CSS classes to your app.css file:
 *
 * @layer utilities {
 *     .fade-in {
 *         animation: fadeIn 0.6s ease-in forwards;
 *     }
 *
 *     @keyframes fadeIn {
 *         from {
 *             opacity: 0;
 *             transform: translateY(20px);
 *         }
 *         to {
 *             opacity: 1;
 *             transform: translateY(0);
 *         }
 *     }
 *
 *     section {
 *         opacity: 0;
 *         transform: translateY(20px);
 *         transition: opacity 0.6s ease, transform 0.6s ease;
 *     }
 *
 *     section.fade-in {
 *         opacity: 1;
 *         transform: translateY(0);
 *     }
 * }
 */

/**
 * Counter animation for stats section
 * Usage: <CountUp end={70} suffix="%" duration={2} />
 */

interface CountUpProps {
    end: number;
    start?: number;
    duration?: number;
    suffix?: string;
    prefix?: string;
}

export function CountUp({ end, start = 0, duration = 2, suffix = '', prefix = '' }: CountUpProps) {
    const [count, setCount] = useState(start);
    const [hasStarted, setHasStarted] = useState(false);

    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting && !hasStarted) {
                    setHasStarted(true);
                }
            },
            { threshold: 0.5 }
        );

        const element = document.getElementById(`count-${end}`);
        if (element) observer.observe(element);

        return () => {
            if (element) observer.unobserve(element);
        };
    }, [end, hasStarted]);

    useEffect(() => {
        if (!hasStarted) return;

        const increment = (end - start) / (duration * 60); // 60 FPS
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= end) {
                setCount(end);
                clearInterval(timer);
            } else {
                setCount(Math.floor(current as number));
            }
        }, 1000 / 60);

        return () => clearInterval(timer);
    }, [hasStarted, start, end, duration]);

    return (
        <span id={`count-${end}`}>
            {prefix}{count}{suffix}
        </span>
    );
}

/**
 * Mobile Menu Component
 * Add this for responsive navigation
 */

export function MobileMenu() {
    const [isOpen, setIsOpen] = useState(false);

    return (
        <div className="md:hidden">
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="text-gray-600 hover:text-emerald-600"
                aria-label="Toggle menu"
            >
                {isOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
            </button>

            {isOpen && (
                <div className="absolute left-0 right-0 top-full z-40 border-b border-emerald-100 bg-white shadow-lg dark:border-emerald-900/30 dark:bg-gray-900">
                    <div className="flex flex-col space-y-4 p-6">
                        <a
                            href="#features"
                            onClick={() => setIsOpen(false)}
                            className="text-sm font-medium text-gray-600 transition-colors hover:text-emerald-600"
                        >
                            Features
                        </a>
                        <a
                            href="#benefits"
                            onClick={() => setIsOpen(false)}
                            className="text-sm font-medium text-gray-600 transition-colors hover:text-emerald-600"
                        >
                            Benefits
                        </a>
                        <a
                            href="#pricing"
                            onClick={() => setIsOpen(false)}
                            className="text-sm font-medium text-gray-600 transition-colors hover:text-emerald-600"
                        >
                            Pricing
                        </a>
                        <a
                            href="#use-cases"
                            onClick={() => setIsOpen(false)}
                            className="text-sm font-medium text-gray-600 transition-colors hover:text-emerald-600"
                        >
                            Use Cases
                        </a>
                    </div>
                </div>
            )}
        </div>
    );
}

/**
 * Newsletter Signup Component
 * Add this to capture email leads
 */
export function NewsletterSignup() {
    const [email, setEmail] = useState('');
    const [status, setStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setStatus('loading');

        // Replace with your actual API endpoint
        try {
            const response = await fetch('/api/newsletter', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email }),
            });

            if (response.ok) {
                setStatus('success');
                setEmail('');
            } else {
                setStatus('error');
            }
        } catch (error) {
            setStatus('error');
        }
    };

    return (
        <div className="mx-auto max-w-md">
            <h3 className="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
                Stay Updated
            </h3>
            <p className="mb-6 text-gray-600 dark:text-gray-300">
                Get the latest features and updates delivered to your inbox.
            </p>
            <form onSubmit={handleSubmit} className="flex gap-2">
                <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    placeholder="Enter your email"
                    required
                    className="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
                <button
                    type="submit"
                    disabled={status === 'loading'}
                    className="rounded-lg bg-emerald-600 px-6 py-2 font-semibold text-white transition-colors hover:bg-emerald-700 disabled:opacity-50"
                >
                    {status === 'loading' ? 'Subscribing...' : 'Subscribe'}
                </button>
            </form>
            {status === 'success' && (
                <p className="mt-2 text-sm text-emerald-600">
                    Thanks for subscribing!
                </p>
            )}
            {status === 'error' && (
                <p className="mt-2 text-sm text-red-600">
                    Something went wrong. Please try again.
                </p>
            )}
        </div>
    );
}

/**
 * Video Modal Component
 * Add this for demo video display
 */
import { Dialog } from '@headlessui/react';

interface VideoModalProps {
    isOpen: boolean;
    onClose: () => void;
    videoUrl: string;
}

export function VideoModal({ isOpen, onClose, videoUrl }: VideoModalProps) {
    return (
        <Dialog open={isOpen} onClose={onClose} className="relative z-50">
            <div className="fixed inset-0 bg-black/70" aria-hidden="true" />

            <div className="fixed inset-0 flex items-center justify-center p-4">
                <Dialog.Panel className="mx-auto max-w-4xl w-full rounded-lg bg-white p-4">
                    <div className="flex justify-between items-center mb-4">
                        <Dialog.Title className="text-xl font-bold">
                            MaiDuka Demo
                        </Dialog.Title>
                        <button
                            onClick={onClose}
                            className="text-gray-500 hover:text-gray-700"
                        >
                            <X className="h-6 w-6" />
                        </button>
                    </div>

                    <div className="aspect-video">
                        <iframe
                            src={videoUrl}
                            className="w-full h-full rounded"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowFullScreen
                        />
                    </div>
                </Dialog.Panel>
            </div>
        </Dialog>
    );
}

/**
 * Testimonial Carousel Component
 * Add this to showcase customer reviews
 */
interface Testimonial {
    name: string;
    role: string;
    company: string;
    content: string;
    avatar?: string;
}

export function TestimonialCarousel({ testimonials }: { testimonials: Testimonial[] }) {
    const [current, setCurrent] = useState(0);

    useEffect(() => {
        const timer = setInterval(() => {
            setCurrent((prev) => (prev + 1) % testimonials.length);
        }, 5000);

        return () => clearInterval(timer);
    }, [testimonials.length]);

    const testimonial = testimonials[current];

    return (
        <div className="relative mx-auto max-w-3xl rounded-2xl border border-emerald-200 bg-white p-8 shadow-lg dark:border-emerald-800 dark:bg-gray-800">
            <div className="mb-6 text-4xl text-emerald-600">"</div>
            <p className="mb-6 text-lg italic text-gray-700 dark:text-gray-300">
                {testimonial.content}
            </p>
            <div className="flex items-center">
                {testimonial.avatar && (
                    <img
                        src={testimonial.avatar}
                        alt={testimonial.name}
                        className="mr-4 h-12 w-12 rounded-full"
                    />
                )}
                <div>
                    <div className="font-semibold text-gray-900 dark:text-white">
                        {testimonial.name}
                    </div>
                    <div className="text-sm text-gray-600 dark:text-gray-400">
                        {testimonial.role} at {testimonial.company}
                    </div>
                </div>
            </div>

            <div className="mt-6 flex justify-center gap-2">
                {testimonials.map((_, index) => (
                    <button
                        key={index}
                        onClick={() => setCurrent(index)}
                        className={`h-2 w-2 rounded-full transition-all ${
                            index === current
                                ? 'w-8 bg-emerald-600'
                                : 'bg-gray-300'
                        }`}
                        aria-label={`Go to testimonial ${index + 1}`}
                    />
                ))}
            </div>
        </div>
    );
}

// All components are exported individually above
// You can import them like: import { useSmoothScroll, ScrollToTop } from '@/components/landing-enhancements';
