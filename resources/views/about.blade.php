@extends('layouts.app')

@push('styles')
<style>
    .about-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
        position: relative;
        z-index: 1;
    }
    
    .about-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        text-align: center;
        font-size: 2.2rem;
        margin-bottom: 30px;
        letter-spacing: 3px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .about-section {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 40px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 30px rgba(138, 43, 226, 0.3);
        backdrop-filter: blur(10px);
    }
    
    .section-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.5rem;
        letter-spacing: 2px;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100px;
        height: 2px;
        background: linear-gradient(to right, rgba(138, 43, 226, 0.7), transparent);
    }
    
    .about-text {
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.8;
        margin-bottom: 20px;
    }
    
    .privacy-accordion {
        margin-top: 20px;
    }
    
    .privacy-item {
        margin-bottom: 15px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .privacy-header {
        background: rgba(30, 30, 60, 0.8);
        padding: 15px 20px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .privacy-header:hover {
        background: rgba(50, 30, 90, 0.8);
    }
    
    .privacy-title {
        margin: 0;
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 1.1rem;
    }
    
    .privacy-icon {
        color: #d8b5ff;
        transition: transform 0.3s ease;
    }
    
    .privacy-content {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease, padding 0.5s ease;
    }
    
    .privacy-content-inner {
        padding: 0 20px;
    }
    
    .privacy-item.active .privacy-content {
        padding: 20px;
        max-height: 1000px;
    }
    
    .privacy-item.active .privacy-icon {
        transform: rotate(180deg);
    }
    
    .mystical-quote {
        font-style: italic;
        color: #d8b5ff;
        text-align: center;
        padding: 20px;
        margin: 30px 0;
        font-size: 1.1rem;
        position: relative;
    }
    
    .mystical-quote:before, .mystical-quote:after {
        content: '"';
        font-size: 3rem;
        color: rgba(138, 43, 226, 0.3);
        position: absolute;
        line-height: 0;
    }
    
    .mystical-quote:before {
        top: 25px;
        left: 0;
    }
    
    .mystical-quote:after {
        bottom: 0;
        right: 0;
    }
    
    .cosmic-emoji {
        display: inline-block;
        margin: 0 5px;
    }
    
    .copyright-notice {
        font-weight: 500;
        color: #d8b5ff;
        text-align: center;
        margin-bottom: 20px;
    }
    
    @media (max-width: 767px) {
        .about-title {
            font-size: 1.8rem;
        }
        
        .section-title {
            font-size: 1.3rem;
        }
        
        .about-section {
            padding: 20px;
        }
        
        .mystical-quote {
            font-size: 1rem;
            padding: 15px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const privacyHeaders = document.querySelectorAll('.privacy-header');
        
        privacyHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const item = this.parentElement;
                item.classList.toggle('active');
            });
        });
    });
</script>
@endpush

@section('content')
<div class="about-container">
    <h1 class="about-title">ABOUT THE JOURNEY</h1>
    
    <div class="about-section">
        <h2 class="section-title">The Master Magical Key</h2>
        <p class="about-text">
            Welcome to "The Master Magical Key to the Universe" – a transformative digital book experience that unveils ancient wisdom and mystical knowledge through a chapter-by-chapter journey. This project originated from the desire to share universal truths that have been guarded by mystics, philosophers, and spiritual teachers throughout the ages.
        </p>
        <p class="about-text">
            Each chapter of this digital book contains carefully crafted insights designed to unlock new levels of understanding about yourself and the universe. The knowledge presented here bridges esoteric traditions with contemporary understanding, offering practical wisdom for navigating your spiritual path.
        </p>
        <div class="mystical-quote">
            "The key to the universe lies not in the stars above, but in the consciousness within."
        </div>
    </div>
    
    <div class="about-section">
        <h2 class="section-title">Copyright Notice <span class="cosmic-emoji">✨</span></h2>
        <p class="copyright-notice">Copyright © 2025 Chanell Donnolley. All Rights Reserved.</p>
        <p class="about-text">
            Greetings, mystical traveler of the written word! You have now entered the realm of Your Master Magical Key to the Universe, a book so powerful that even the Universe itself whispered, "Damn, that's good."
        </p>
        <p class="about-text">
            Now, let's get serious (but not too serious). This book, along with its spells, cosmic teachings, and reality-altering wisdom, is protected by the sacred laws of copyright. That means:
        </p>
        <ul class="about-text">
            <li>No photocopying it to create your own secret society.</li>
            <li>No recording it for your late-night ASMR YouTube channel.</li>
            <li>No rebranding it as "A Totally Original Guide to Manifesting Everything".</li>
            <li>No attempting to accidentally "channel" my words into your own work while claiming "divine inspiration" (the spirits told me they'll snitch).</li>
        </ul>
        <p class="about-text">
            You may (with my blessing) quote small sections if you're writing a review, praising my brilliance, or using it for educational purposes—just make sure you give proper credit or risk being hexed with eternal bad Wi-Fi. (joking!)
        </p>
        <p class="about-text">
            For licensing inquiries, permissions, or to shower me with praise and offerings, please contact:
            <br>📧 support@peopleofpeony.com
        </p>
        <p class="about-text">
            This book and all associated materials operate under People of Peony PTY Ltd (ABN 35629544921), so let's keep things cosmic, ethical, and legally tidy.
        </p>
        <p class="about-text">
            Until then, may your manifestations be strong, your energy high-vibrational, and your ability to follow copyright laws impeccable.
        </p>
        <p class="about-text" style="text-align: right;">
            Blessings & Boundaries,<br>
            <span class="cosmic-emoji">✨</span> Chanell Donnolley, Guardian of the Cosmic Keys & Copyrights <span class="cosmic-emoji">✨</span>
        </p>
    </div>
    
    <div class="about-section">
        <h2 class="section-title">Terms & Conditions <span class="cosmic-emoji">🔮</span></h2>
        
        <div class="privacy-accordion">
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">1. Introduction</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">These Terms & Conditions ("Terms") govern the purchase and use of Your Master Magical Key to the Universe and any associated products, including books, courses, digital downloads, workshops, and coaching services (collectively, "Services"). By purchasing or using these Services, you agree to these Terms.</p>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">2. Intellectual Property Rights</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">All content, including text, graphics, logos, audio, and digital downloads, is the exclusive property of Chanell Donnolley and is protected under Australian copyright and intellectual property laws.</p>
                        <p class="about-text">You may not reproduce, resell, or distribute any material without express written permission (yes, even if you "really resonate with it").</p>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">3. Payment & No Refunds (Yes, We Mean It)</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">ALL SALES ARE FINAL. NO REFUNDS. NO TAKE-BACKS. NO MAGICALLY UN-MANIFESTING PURCHASES.</p>
                        <p class="about-text">Payment is required in full at the time of purchase unless a payment plan is offered (and if you fail to make payments, your access to materials will be revoked faster than your ex ghosted you).</p>
                        <p class="about-text">Refunds? Not happening. This isn't a try-before-you-buy situation—transformation requires commitment.</p>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">4. Disclaimer of Results</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">These materials are for educational and spiritual growth purposes only.</p>
                        <p class="about-text">There are no guarantees regarding personal outcomes, financial success, or manifestation results. (If it was that easy, we'd all own a yacht by now.)</p>
                        <p class="about-text">These materials are not a substitute for medical, psychological, financial, or legal advice. If you need a doctor, therapist, accountant, or lawyer—seek a professional, not a spirit guide.</p>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">5. Privacy & Data Protection</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">Your personal information will be collected and stored in compliance with Australian privacy laws (see our Privacy Policy).</p>
                        <p class="about-text">We will never sell, trade, or misuse your data—because that's just bad karma.</p>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">6. Governing Law & Disputes</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">These Terms shall be governed by and interpreted in accordance with the laws of Queensland, Australia.</p>
                        <p class="about-text">Any disputes shall be resolved through mediation first, before legal action is considered (because nobody needs that stress).</p>
                        <p class="about-text">For questions, cosmic concerns, or licensing inquiries, please contact:</p>
                        <p class="about-text">📧 support@peopleofpeony.com</p>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="about-text" style="text-align: center; margin-top: 20px;">
            <span class="cosmic-emoji">✨</span> By purchasing, you acknowledge that you've read, understood, and accepted these Terms. No backsies! <span class="cosmic-emoji">✨</span>
        </p>
    </div>
    
    <div class="about-section">
        <h2 class="section-title">Privacy Policy <span class="cosmic-emoji">🔮</span></h2>
        <p class="about-text">
            Welcome, mystical traveler! Here at People of Peony PTY Ltd (ABN 35629544921), we take privacy as seriously as spellwork. Just like a sacred circle protects energy, this Privacy Policy safeguards your personal information. Read on to discover how we collect, use, and honor your data.
        </p>
        
        <div class="privacy-accordion">
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">1. The Sacred Scrolls of Information We Collect</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">To ensure your magical journey runs smoothly, we collect the following enchanted data:</p>
                        <ul class="about-text">
                            <li><span class="cosmic-emoji">✨</span> Your Name & Contact Details – So we can whisper cosmic updates (aka emails) and deliver your magical goods.</li>
                            <li><span class="cosmic-emoji">✨</span> Payment Information – Processed securely via PayPal (we never store your financial details—only the Universe holds that power).</li>
                            <li><span class="cosmic-emoji">✨</span> Technical Energies – Your IP address, browser type, and website interactions (captured via cookies, because even the internet has its own form of divination).</li>
                            <li><span class="cosmic-emoji">✨</span> Offerings You Share – Any wisdom you voluntarily provide during coaching, workshops, or courses.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">2. How We Use Your Energy (Ahem, Data)</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">Like a well-crafted ritual, we use your personal data to:</p>
                        <ul class="about-text">
                            <li><span class="cosmic-emoji">🌟</span> Deliver your magical purchases (courses, books, and workshops).</li>
                            <li><span class="cosmic-emoji">🌟</span> Send celestial updates, promotions, and insights (only if you've consented—no unwanted energetic intrusions here).</li>
                            <li><span class="cosmic-emoji">🌟</span> Enhance your experience & refine our offerings (because growth is part of the journey).</li>
                            <li><span class="cosmic-emoji">🌟</span> Honour legal & financial obligations (even magic has its laws).</li>
                        </ul>
                        <p class="about-text">You can opt out of our mystical emails anytime—just hit the "unsubscribe" link in our messages or send us a telepathic… er, digital request.</p>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">3. Protection Spells for Your Data</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">Your privacy is sacred. We use powerful wards (a.k.a. security measures) to keep your data safe, including:</p>
                        <ul class="about-text">
                            <li><span class="cosmic-emoji">🛡</span> Encryption where necessary to shield sensitive data.</li>
                            <li><span class="cosmic-emoji">🛡</span> Strict access control—only trusted guardians of People of Peony can access your details.</li>
                            <li><span class="cosmic-emoji">🛡</span> PayPal-secured transactions—your payments are processed through PayPal's protective fortress, not stored by us.</li>
                        </ul>
                        <p class="about-text">We never sell, trade, or misuse your information—bad karma isn't worth it.</p>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">4. Your Rights & Magical Powers</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">Under Australian privacy laws (and GDPR, if applicable), you have the right to:</p>
                        <ul class="about-text">
                            <li><span class="cosmic-emoji">🔮</span> Access, edit, or erase your personal data upon request.</li>
                            <li><span class="cosmic-emoji">🔮</span> Unsubscribe from our cosmic transmissions (marketing emails).</li>
                            <li><span class="cosmic-emoji">🔮</span> Request data portability (in case you want to take your data to another magical realm).</li>
                            <li><span class="cosmic-emoji">🔮</span> Restrict or object to certain types of processing.</li>
                        </ul>
                        <p class="about-text">To wield these rights, simply send your request to 📧 support@peopleofpeony.com.</p>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">5. The Third-Party Alliances</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">We work with trustworthy allies to ensure smooth operations:</p>
                        <ul class="about-text">
                            <li><span class="cosmic-emoji">🌙</span> PayPal – Our payment wizard of choice, securely handling all transactions.</li>
                            <li><span class="cosmic-emoji">🌙</span> Cookies & Analytics – Tools like Google Analytics help us improve your experience (manage cookies in your browser settings).</li>
                        </ul>
                        <p class="about-text">These third-party providers have their own privacy policies—always check their scrolls (aka Terms & Conditions).</p>
                    </div>
                </div>
            </div>
            
            <div class="privacy-item">
                <div class="privacy-header">
                    <h3 class="privacy-title">6. Changes to This Sacred Pact</h3>
                    <span class="privacy-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="privacy-content">
                    <div class="privacy-content-inner">
                        <p class="about-text">If we update this Privacy Policy, the changes will be posted on our website. If it's a major shift in the energetic balance (or data use), we'll let you know directly.</p>
                        <p class="about-text">For any privacy-related questions, protection spells, or concerns, reach out to us at:</p>
                        <p class="about-text">📧 support@peopleofpeony.com</p>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="about-text" style="text-align: center; margin-top: 20px;">
            <span class="cosmic-emoji">✨</span> By using our Services, you accept this Privacy Policy. May your manifestations be powerful, your data protected, and your inbox only filled with things that truly serve you. <span class="cosmic-emoji">✨</span>
        </p>
        
        <p class="about-text" style="text-align: right;">
            With love & cosmic security,<br>
            <span class="cosmic-emoji">🌿</span> People of Peony PTY Ltd <span class="cosmic-emoji">🌿</span>
        </p>
    </div>
    
    <div class="about-section">
        <h2 class="section-title">Last Updated</h2>
        <p class="about-text">
            These policies were last updated on March 10, 2025.
        </p>
    </div>
</div>
@endsection