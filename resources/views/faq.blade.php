@extends('layouts.app')

@push('styles')
<style>
    .faq-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
        position: relative;
        z-index: 1;
    }
    
    .faq-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        text-align: center;
        font-size: 5rem;
        margin-bottom: 30px;
        letter-spacing: 3px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
        text-align: left;
    }
    
    .faq-section {
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
    
    .faq-text {
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.8;
        margin-bottom: 20px;
    }
    
    .faq-accordion {
        margin-top: 20px;
    }
    
    .faq-item {
        margin-bottom: 15px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .faq-header {
        background: rgba(30, 30, 60, 0.8);
        padding: 15px 20px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .faq-header:hover {
        background: rgba(50, 30, 90, 0.8);
    }
    
    .faq-title {
        margin: 0;
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 1.1rem;
    }
    
    .faq-icon {
        color: #d8b5ff;
        transition: transform 0.3s ease;
    }
    
    .faq-content {
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease, padding 0.5s ease;
    }
    
    .faq-content-inner {
        padding: 0 20px;
    }
    
    .faq-item.active .faq-content {
        padding: 20px;
        max-height: 1000px;
    }
    
    .faq-item.active .faq-icon {
        transform: rotate(180deg);
    }
    
    .cosmic-emoji {
        display: inline-block;
        margin: 0 5px;
    }
    
    .faq-highlight {
        color: #d8b5ff;
        font-weight: 500;
    }
    
    .faq-bullet-list {
        list-style-type: none;
        padding-left: 25px;
    }
    
    .faq-bullet-list li {
        position: relative;
        margin-bottom: 15px;
    }
    
    .faq-bullet-list li:before {
        content: '✨';
        position: absolute;
        left: -25px;
        top: 0;
    }
    
    @media (max-width: 767px) {
        .faq-title {
            font-size: 1rem;
        }
        
        .section-title {
            font-size: 1.3rem;
        }
        
        .faq-section {
            padding: 20px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const faqHeaders = document.querySelectorAll('.faq-header');
        
        faqHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const item = this.parentElement;
                item.classList.toggle('active');
            });
        });
    });
</script>
@endpush

@section('content')
<div class="faq-container">
    <h1>FREQUENTLY ASKED QUESTIONS</h1>
    
    <div class="faq-section">
        <h2 class="section-title">About The Master Magical Key <span class="cosmic-emoji">🗝️</span></h2>
        <p class="faq-text">
            Whether you're a spiritual rebel, energetic healer, multi-dimensional entrepreneur, or just someone who <em>knows</em> you're here to do life differently—this book isn't just pages. It's a portal.
        </p>
        <p class="faq-text">
            Inside, you'll find spells, codes, activations, and tools designed to unlock your magic, rewire your reality, and awaken the creator within you.
        </p>
        <p class="faq-text">
            This is for anyone who feels the pull of something bigger—no matter your gender, identity, background, or starting point.<br>
            If you're ready to stop playing small and start bending the universe to your will...<br>
            Welcome home.
        </p>
        
        <div class="faq-accordion">
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">What is The Master Magical Key to the Universe?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>It's not just a book—it's a full-body, frequency-shifting manifestation portal.<br>
                            A self-activation journey through belief, shadow work, energetic embodiment, trauma alchemy, sex magic, and reality hacking.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>This is your sacred map to becoming a conscious creator of your own universe.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>Whether you're a witch, healer, rebel, visionary, lightworker, spiritual entrepreneur, energy worker, or just someone who knows they were born for <em>more</em>—this book is your initiation.<br>
                            It's already being called the best manifestation book for modern mystics and multidimensional beings ready to stop waiting, start commanding, and step into full alignment.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>Inside, you'll unlock:</strong>
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong>How to manifest using Human Design & the Enneagram for transformation</strong></li>
                            <li><strong>How to activate spiritual gifts and stop playing small in your life, body, and business</strong></li>
                            <li><strong>How to attract money with ease by turning on your unique wealth codes</strong></li>
                            <li><strong>How to master the Laws of the Universe (and stop getting dragged by karma)</strong></li>
                            <li><strong>How to channel pleasure into purpose through powerful sex magic rituals & orgasmic manifestation</strong></li>
                        </ul>
                        <p class="faq-text">
                            <strong>This book doesn't just teach you magic—it activates the version of you who already knows how to use it.</strong>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">Who is The Master Magical Key to the Universe for?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>This book is for every soul who's ever felt the pull of the cosmos and whispered,<br>
                            "There has to be more than this."</strong>
                        </p>
                        <p class="faq-text">
                            <strong>If you've ever typed into Google:</strong>
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong><em>how to manifest faster</em></strong></li>
                            <li><strong><em>how to align with abundance</em></strong></li>
                            <li><strong><em>how to quantum leap your reality</em></strong></li>
                            <li><strong><em>how to activate wealth codes and attract unexpected money</em></strong></li>
                            <li><strong><em>how to use sex magic for manifestation and business success</em></strong></li>
                            <li><strong><em>manifestation using Human Design or the Enneagram</em></strong></li>
                        </ul>
                        <p class="faq-text">
                            <strong>Then love... this is your spellbook.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>It's for witches, mystics, healers, CEOs, creators, lightworkers, and rebels of all genders.<br>
                            It's for the quantum dreamers, the sensual beings, the shadow alchemists, the sacred misfits, and the spiritual baddies ready to reclaim their inner power and manifest with precision.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>This isn't for people who want a feel-good affirmation and hope for the best.<br>
                            This is for the ones who came to rewrite timelines, bend universal law, and become the spell.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>If you're here? You're ready.</strong>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">What makes The Master Magical Key different from other manifestation books?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>Because this isn't some surface-level, fluff-filled, "think positive and wait" Law of Attraction-lite bullshit.<br>
                            This is wizard-level manifestation mastery rooted in quantum precision, ancient energetic systems, and embodiment.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>This is the book that finally explains <em>how manifestation actually works</em>—energetically, psychologically, spiritually, and sexually.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>Inside, you'll learn how to:</strong>
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong>Manifest based on your Human Design energy type—no more trying to copy someone else's path</strong></li>
                            <li><strong>Use the Enneagram to identify your manifesting strengths, shadows, and energy leaks</strong></li>
                            <li><strong>Turn orgasms into wealth portals with real, grounded sex magic that works</strong></li>
                            <li><strong>Clear limiting beliefs, scarcity codes, and ancestral trauma through shadow work</strong></li>
                            <li><strong>Activate belief magic, business manifestation rituals, and pleasure-based prosperity</strong></li>
                        </ul>
                        <p class="faq-text">
                            <strong>This isn't "just visualize and wait." This is energetic engineering.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>It blends:</strong>
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong>Ancient wisdom from esoteric systems (like astrology, Kabbalah & I Ching)</strong></li>
                            <li><strong>Quantum manifestation methods backed by frequency & intention</strong></li>
                            <li><strong>Feminine energy healing and chakra-based money magnetism</strong></li>
                            <li><strong>Real rituals that shift your vibration instantly</strong></li>
                        </ul>
                        <p class="faq-text">
                            <strong>If you've tried to manifest and it hasn't worked—it's not because you're broken.<br>
                            It's because no one gave you the damn manual.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>Until now.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>This book is the <em>manual</em>. The <em>map</em>. The <em>key</em>.</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="faq-section">
        <h2 class="section-title">Manifestation Techniques <span class="cosmic-emoji">✨</span></h2>
        
        <div class="faq-accordion">
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">Do I need to already be spiritual or into magic?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>Not even a little. This book meets you exactly where you are—whether you're seasoned in spellwork or just starting to Google "how to manifest money fast."</strong>
                        </p>
                        <p class="faq-text">
                            <strong>You don't need crystals, moon rituals, or to identify as a witch.<br>
                            You just need to be open to remembering your power.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>Whether you're:</strong>
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong>New to manifestation and not sure where to start</strong></li>
                            <li><strong>Curious about energy healing, but not into the "woo"</strong></li>
                            <li><strong>Looking for real manifestation techniques that actually work</strong></li>
                            <li><strong>Healing from religious trauma but still craving something <em>more</em></strong></li>
                        </ul>
                        <p class="faq-text">
                            <strong>...this book is for you.</strong>
                        </p>
                        <p class="faq-text">
                            <strong>You'll go from "is this real?" to "holy f*ck, I just manifested that" with:</strong>
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong>Real-time energetic activations (you'll feel shifts as you read)</strong></li>
                            <li><strong>Step-by-step manifestation practices for beginners and pros alike</strong></li>
                            <li><strong>Manifestation methods without needing to be high-vibe all the time</strong></li>
                            <li><strong>Shadow integration, belief reprogramming, and energetic rewiring</strong></li>
                        </ul>
                        <p class="faq-text">
                            <strong>This is magic made for the modern world.<br>
                            No gatekeeping. No spiritual elitism.<br>
                            Just pure, practical, powerful manifestation that works for everyone.</strong>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">What is the fastest way to manifest money?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>Manifesting money fast doesn't come from hustle—it comes from turn-on.</strong><br>
                            You attract wealth when your frequency is high, your body is open, and your belief is locked in. This book teaches you how to use <strong>sex magic for wealth</strong>, <strong>pleasure-based manifestation techniques</strong>, and <strong>energetic rituals to call in abundance</strong> without burnout.
                        </p>
                        <p class="faq-text">
                            If you've Googled:
                        </p>
                        <ul class="faq-bullet-list">
                            <li><em>how to manifest unexpected money</em></li>
                            <li><em>how to attract wealth with energy</em></li>
                            <li><em>money manifestation rituals that work fast</em></li>
                        </ul>
                        <p class="faq-text">
                            ...you're in the right place.
                        </p>
                        <p class="faq-text">
                            Inside, you'll learn to manifest money through:
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong>Belief coding</strong></li>
                            <li><strong>Quantum energy work</strong></li>
                            <li><strong>Root + sacral chakra activation</strong></li>
                            <li><strong>Pleasure as currency</strong></li>
                            <li>And yes—<strong>orgasmic wealth magnetism</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">How do I raise my vibration to attract abundance?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            Not through fake smiles and toxic positivity. You raise your vibration by getting <em>real</em>, <em>raw</em>, and <em>energetically regulated</em>.<br>
                            In Chapter 3, I walk you through how to <strong>raise your vibration to attract wealth</strong>, <strong>align with abundance</strong>, and <strong>rewire your energetic field</strong> using tools that are <strong>nervous system safe</strong>, <strong>spiritually sound</strong>, and <strong>energetically potent</strong>.
                        </p>
                        <p class="faq-text">
                            You'll unlock:
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong>Daily manifestation rituals to increase vibration</strong></li>
                            <li><strong>Energy healing for money blocks + self-worth wounds</strong></li>
                            <li><strong>High-frequency mindset practices</strong> that stick</li>
                            <li><strong>Feminine energy practices</strong> for receiving</li>
                        </ul>
                        <p class="faq-text">
                            Searches like <em>how to raise your frequency</em>, <em>how to align with abundance</em>, and <em>best high-vibe daily rituals</em>? They land people right here.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">Can I use Human Design to manifest?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>Hell yes—and it's a total game changer.</strong><br>
                            Most people are trying to manifest using strategies that don't match their energetic design. In this book, I decode exactly how to manifest with your <strong>Human Design type</strong>, <strong>Strategy</strong>, and <strong>Authority</strong>.
                        </p>
                        <p class="faq-text">
                            You'll learn how to:
                        </p>
                        <ul class="faq-bullet-list">
                            <li>Use <strong>Manifestor energy</strong> to initiate new realities</li>
                            <li>Let <strong>Generator & MG types</strong> master manifestation through response + desire</li>
                            <li>Guide energy as a <strong>Projector</strong> to manifest success</li>
                            <li>Align with the moon as a <strong>Reflector</strong> for precision manifestation</li>
                            <li>Activate your <strong>aura</strong> as a frequency field of magnetism</li>
                            <li>Use your <strong>energy centers + profile</strong> for tailored abundance techniques</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">Do sex magic rituals actually work?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>If done with clarity, intention, and power—yes. And they work FAST.</strong><br>
                            Sex magic is one of the most powerful (and least understood) manifestation tools on the planet. Inside this book, you'll learn exactly how to use <strong>orgasmic energy</strong>, <strong>sensual embodiment</strong>, and <strong>intentional climax</strong> to open manifestation portals.
                        </p>
                        <p class="faq-text">
                            You'll discover:
                        </p>
                        <ul class="faq-bullet-list">
                            <li>How to create a <strong>sex magic ritual for money</strong></li>
                            <li>How to use your <strong>orgasm to amplify intentions</strong></li>
                            <li>How to combine <strong>manifestation + sacred sexuality</strong> for next-level power</li>
                            <li>How to transform shame into fuel and <strong>turn your pleasure into prosperity</strong></li>
                        </ul>
                        <p class="faq-text">
                            Top search keywords that bring people here: <em>does sex magic work</em>, <em>how to manifest using orgasm</em>, <em>tantric manifestation techniques</em>, <em>sacred sexuality for money and love</em>
                        </p>
                        <p class="faq-text">
                            And inside the book? You get the exact codes. No gatekeeping. No guru required.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="faq-section">
        <h2 class="section-title">About the Book <span class="cosmic-emoji">📚</span></h2>
        
        <div class="faq-accordion">
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">Will this help me with money, love, business, and self-confidence?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>YES. And then some.<br>
                            <em>The Master Magical Key to the Universe</em> is your all-in-one manifestation toolkit—designed to help you manifest love, money, success, and unshakable self-worth <em>on your terms</em>.</strong>
                        </p>
                        <p class="faq-text">
                            Inside this book, you'll activate:
                        </p>
                        <p class="faq-text">
                            <strong>💸 Money manifestation magic & financial abundance rituals</strong><br>
                            Learn how to shift your frequency to attract aligned income, overflow, and unexpected wealth. Activate your wealth codes and start calling in cash <em>without burnout</em>.
                        </p>
                        <p class="faq-text">
                            <strong>💋 Manifesting love & divine relationships through energetic alignment</strong><br>
                            Whether you're calling in a soulmate, divine counterpart, or deeper intimacy in your current relationship, you'll learn to become magnetic through embodiment and frequency mastery.
                        </p>
                        <p class="faq-text">
                            <strong>🖤 Self-love, confidence & energy healing techniques</strong><br>
                            Rewire your nervous system, break up with lack and shame, and remember who you are—powerful, radiant, and already enough.
                        </p>
                        <p class="faq-text">
                            <strong>💼 Business manifestation strategies for soulpreneurs, coaches & creators</strong><br>
                            Turn your offers into spells. Use your sexual energy, belief system, and Human Design to magnetize clients, visibility, and purpose-driven success.
                        </p>
                        <p class="faq-text">
                            Whether you're manifesting love, clients, cash, clarity, or your next timeline leap—this isn't just a read.<br>
                            It's your next <strong>quantum jump</strong>.<br>
                            Your <strong>frequency shift</strong>.<br>
                            Your <strong>YES to the universe.</strong>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">What format is The Master Magical Key to the Universe available in?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>However you like to consume your magic, we've got you covered:</strong>
                        </p>
                        <p class="faq-text">
                            <strong>🌐 Digital Format — Instant Access</strong><br>
                            Get started immediately with the full manifestation book in downloadable digital format. No waiting. Just pure, spell-binding activation the moment you say <em>yes</em>.
                        </p>
                        <p class="faq-text">
                            <strong>🔮 Includes Powerful Bonuses</strong><br>
                            You'll receive a sacred library of:
                        </p>
                        <ul class="faq-bullet-list">
                            <li>Spells and rituals for sex magic, money magic, belief rewiring, and energetic embodiment</li>
                            <li>Energetic journal prompts to unlock your deepest truths</li>
                            <li>Manifestation worksheets designed to rewire your subconscious and raise your vibration</li>
                        </ul>
                        <p class="faq-text">
                            <strong>🎧 FREE Audiobook</strong><br>
                            For all my audio-obsessed babes and multi-tasking wizards—yes, a guided audio version with full-body transmission energy is on the way.
                        </p>
                        <p class="faq-text">
                            <strong>📦 Physical Copy — Coming Soon</strong><br>
                            A beautifully designed print edition is in the works, featuring bonus chapter codes, ritual-ready layout, and maybe even a <em>limited edition altar-card insert</em> (👀 stay tuned).
                        </p>
                        <p class="faq-text">
                            This is not just a book—it's a choose-your-own portal of manifestation.<br>
                            However you like to receive information, the Universe will meet you there.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">Can I gift this to someone?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            Absolutely.<br>
                            It's the perfect gift for:
                        </p>
                        <ul class="faq-bullet-list">
                            <li>Your bestie who's "so intuitive but doesn't know it yet"</li>
                            <li>Your partner who's ready to explore <strong>energetic sex</strong></li>
                            <li>Your sister who's into journaling and moon rituals</li>
                            <li>Your inner child, who always knew magic was real</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">What if I'm not ready?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>That's exactly when the Universe hands you the key.</strong><br>
                            This book was <em>made</em> for the ones who are scared.<br>
                            The ones who are healing, doubting, second-guessing, or silently screaming, "I know I'm meant for more... but how do I get there?"
                        </p>
                        <p class="faq-text">
                            You don't need to be high-vibe.<br>
                            You don't need to have it all figured out.<br>
                            You don't need to be "ready."<br>
                            You just need to <em>say yes</em>.
                        </p>
                        <p class="faq-text">
                            Because the moment you open this book, you open a <strong>manifestation portal</strong>.<br>
                            One that begins rewiring your reality with:
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong>Real manifestation techniques for uncertain souls</strong></li>
                            <li><strong>Subconscious reprogramming for clarity and self-trust</strong></li>
                            <li><strong>Energy healing rituals for fear, resistance, and self-doubt</strong></li>
                            <li><strong>Spiritual tools for beginners and advanced seekers alike</strong></li>
                        </ul>
                        <p class="faq-text">
                            ✨ From that first chapter, your frequency begins to rise.<br>
                            ✨ Synchronicities multiply.<br>
                            ✨ Your <strong>money manifestation magnet</strong> activates.<br>
                            ✨ Your next-level self—the version of you who is confident, powerful, abundant, and <em>aligned AF</em>—comes online.
                        </p>
                        <p class="faq-text">
                            You don't step into your magic because you're ready.<br>
                            You step into your magic because you're <em>done waiting</em>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="faq-section">
        <h2 class="section-title">About the Author <span class="cosmic-emoji">👩‍💫</span></h2>
        
        <div class="faq-accordion">
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-title">Who is the author of The Master Magical Key to the Universe?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>Meet the real-life wizard behind the words: Chanell.</strong>
                        </p>
                        <p class="faq-text">
                            Chanell is not your average manifestation coach or spiritual influencer.<br>
                            She's a multidimensional healer, energy alchemist, intuitive business coach, trauma-informed space holder, and the unapologetic voice behind <em>The Master Magical Key to the Universe</em>—the <strong>most powerful manifestation book for beginners, rebels, and spiritual entrepreneurs</strong> alike.
                        </p>
                        <p class="faq-text">
                            She's built a multi-million dollar empire through raw belief, embodied magic, and rituals that actually f*cking work. Her work combines:
                        </p>
                        <ul class="faq-bullet-list">
                            <li><strong>Human Design manifestation strategy</strong></li>
                            <li><strong>Sex magic rituals for wealth and self-healing</strong></li>
                            <li><strong>Energetic money mindset rewiring for soul-led business owners</strong></li>
                            <li><strong>Feminine energy healing</strong> & chakra alignment</li>
                            <li><strong>Shadow work and trauma alchemy</strong> for deep transformation</li>
                            <li><strong>Law of Assumption, Law of Vibration & Universal Laws</strong> taught through embodiment, not theory</li>
                            <li><strong>Real magic for real people</strong>—with no gatekeeping, no fluff, and no guru complex</li>
                        </ul>
                        <p class="faq-text">
                            If you've searched for:<br>
                            <em>best spiritual life coach for women and queer creators</em><br>
                            <em>energy healer and manifestation expert</em><br>
                            <em>authentic feminine business coach with a trauma-informed approach</em><br>
                            <em>how to use sex magic to manifest money</em><br>
                            <em>intuitive manifestation coach using Human Design and Enneagram</em><br>
                            —then welcome home. You found her.
                        </p>
                        <p class="faq-text">
                            Chanell's teachings aren't light and love—they're truth and transformation. She's here to help you remember who you are, activate your divine coding, and turn your life into a full-body yes.
                        </p>
                        <p class="faq-text">
                            Want to know the best part?<br>
                            She's not special. She's just activated.<br>
                            And this book will do the same for you.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="faq-section">
        <h2 class="section-title">Last Updated</h2>
        <p class="faq-text">
            These Frequently Asked Questions were last updated on March 15, 2025.
        </p>
    </div>
</div>
@endsection