@extends('layouts.app')

@push('styles')
<style>
    .faq-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 30px 20px;
        position: relative;
        z-index: 1;
        margin-bottom: 50px;
    }
    
    .faq-main-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        text-align: center;
        font-size: 1.5rem;
        margin-bottom: 20px;
        letter-spacing: 3px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .faq-intro {
        /* text-align: center; */
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.25;
        margin-bottom: 20px;
        font-style: italic;
    }
    
    .faq-section {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 20px;
        font-size: 14px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 30px rgba(138, 43, 226, 0.3);
        backdrop-filter: blur(10px);
    }
    
    .section-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 14px;
        letter-spacing: 2px;
        margin-bottom: 10px;
        position: relative;
        padding-bottom: 5px;
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
        line-height: 1.25;
        margin-bottom: 20px;
    }
    
    .faq-accordion {
        margin-top: 20px;
    }
    
    .faq-item {
        margin-bottom: 10px;
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
    
    .faq-question {
        margin: 0;
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 14px;
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
        max-height: 2000px;
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
        .faq-main-title {
            font-size: 1.5rem;
        }
        
        .section-title {
            font-size: 1rem;
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
    <h1 class="faq-main-title">THE AGREEMENTS 🤍</h1>
    
    <div class="faq-section">
        <div class="faq-intro">
            <p>(read this like a conversation, not a contract)</p>
            <p>before we go any further, let's get on the same page 😮‍💨<br>
            not because i'm controlling.<br>
            not because you're in trouble.<br>
            but because this work is powerful 🤍 and power needs clarity.</p>
            <p>these aren't rules.<br>
            they're agreements.<br>
            with yourself 🤍 with me 🤍 with the framework.</p>
        </div>
        
        <div class="faq-accordion">
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">what this actually is 🤍</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            this is not a belief system.<br>
                            it's not a religion.<br>
                            it's not a cult.<br>
                            it's not a spiritual hierarchy.
                        </p>
                        <p class="faq-text">
                            i don't belong to a lineage, doctrine, or authority and neither do you.<br>
                            i have no master (dobby has no master 🧦✨).<br>
                            there's no worship here. no devotion. no chosen ones.
                        </p>
                        <p class="faq-text">
                            this is an intelligence framework 🧠✨<br>
                            made up of three distinct layers<br>
                            each with a different role<br>
                            each activated in the right order.
                        </p>
                        <p class="faq-text">
                            it works whether you believe in it or not.<br>
                            because it's built on law, not opinion.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">sovereignty is non negotiable 👑</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            you don't have to believe anything.<br>
                            you don't have to change who you are.<br>
                            you don't have to adopt my worldview, my language, or my lifestyle.
                        </p>
                        <p class="faq-text">
                            take what works 🤍 leave what doesn't.<br>
                            you remain sovereign at all times.
                        </p>
                        <p class="faq-text">
                            this framework does not replace your agency.<br>
                            it strengthens it.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">do i need to be healed, calm, spiritual, or ready?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            no.
                        </p>
                        <p class="faq-text">
                            you can come tired 😴<br>
                            skeptical 😏<br>
                            grieving 💔<br>
                            ambitious 🔥<br>
                            or messy 😘
                        </p>
                        <p class="faq-text">
                            this work does not respond to mood.<br>
                            it responds to order.
                        </p>
                        <p class="faq-text">
                            you do not need to purge, unblock, cleanse, clear, or spiritually exfoliate yourself into dust before you're "allowed" to receive 😮‍💨<br>
                            that model is outdated.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">will this override, hypnotise, or control me?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            hard no.
                        </p>
                        <p class="faq-text">
                            nothing here bypasses your will.<br>
                            nothing here overrides your consent.
                        </p>
                        <p class="faq-text">
                            the master magical keys work with your intelligence 🤍 not over it.<br>
                            you are not being programmed.<br>
                            you are activating intelligence that already exists within you.
                        </p>
                        <p class="faq-text">
                            you are always in choice.<br>
                            always in consent.<br>
                            always in charge.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">what if i'm skeptical or don't believe any of this?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            perfect 🤍 honestly ideal 😘
                        </p>
                        <p class="faq-text">
                            skepticism is information.<br>
                            curiosity is information.<br>
                            resistance is information.<br>
                            even the "who does she think she is?" thought 😌
                        </p>
                        <p class="faq-text">
                            log it. clock it. notice what comes up.<br>
                            we take that information to the map later and cash it in 🗺️✨
                        </p>
                        <p class="faq-text">
                            belief is optional.<br>
                            structure is not.
                        </p>
                        <p class="faq-text">
                            gravity doesn't require belief.<br>
                            and neither does this framework.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">what level am i actually working with right now?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            right now 🤍 you are working with level one.
                        </p>
                        <p class="faq-text">
                            the master magical keys are live<br>
                            and designed to be activated safely by anyone with consent and curiosity.
                        </p>
                        <p class="faq-text">
                            level two 🤍 the path<br>
                            and level three 🤍 the intelligence grid<br>
                            are coming soon<br>
                            and will be introduced only when they are fully embodied, tested, and safe to teach.
                        </p>
                        <p class="faq-text">
                            nothing here is rushed.<br>
                            power unfolds in sequence.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">is this therapy, coaching, or medical advice?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            no.
                        </p>
                        <p class="faq-text">
                            this is not therapy.<br>
                            not psychological treatment.<br>
                            not medical advice.
                        </p>
                        <p class="faq-text">
                            it does not replace professional support.<br>
                            and it does not pretend to.
                        </p>
                        <p class="faq-text">
                            this is a framework for orientation and creation.<br>
                            how you apply it 🤍 where you apply it 🤍 and how fast you move 🤍<br>
                            is your responsibility.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">what results can i expect?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            this is not about guarantees.<br>
                            it's about law.
                        </p>
                        <p class="faq-text">
                            people commonly experience<br>
                            money landing and staying 💸<br>
                            relationships stabilising or resolving cleanly ❤️<br>
                            clarity replacing confusion 🔍<br>
                            confidence replacing anxiety 🧠<br>
                            momentum returning 🚀<br>
                            decisions becoming obvious 😮‍💨
                        </p>
                        <p class="faq-text">
                            not because they tried harder<br>
                            but because their internal system came into order 🧬
                        </p>
                        <p class="faq-text">
                            results depend on<br>
                            how you engage<br>
                            your capacity to integrate<br>
                            and your willingness to take responsibility.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">what if something feels uncomfortable or activating?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            important question 🤍
                        </p>
                        <p class="faq-text">
                            this work can surface truth quickly.<br>
                            not to harm you<br>
                            but to orient you.
                        </p>
                        <p class="faq-text">
                            you control the pace.<br>
                            you can pause.<br>
                            step back.<br>
                            integrate.
                        </p>
                        <p class="faq-text">
                            nothing here demands urgency.<br>
                            nothing here rewards intensity.
                        </p>
                        <p class="faq-text">
                            power does not rush.<br>
                            it stabilises.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">can this be misused?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            yes.<br>
                            like money 💸<br>
                            influence 👀<br>
                            sex 🔥<br>
                            or intelligence 🧠
                        </p>
                        <p class="faq-text">
                            i won't police you.<br>
                            but i will be clear 🤍
                        </p>
                        <p class="faq-text">
                            use this framework with responsibility.
                        </p>
                        <p class="faq-text">
                            this work is about<br>
                            coherence 🤍 not domination<br>
                            creation 🤍 not collapse<br>
                            pleasure 🤍 not harm
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">your responsibility 🤍</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            bring your own spine 🧿<br>
                            bring your own boundaries 🛡️<br>
                            bring your own discernment 👑
                        </p>
                        <p class="faq-text">
                            i bring intelligence you can activate 🔑<br>
                            i bring the map 🗺️<br>
                            and when the time is right 🤍 i'll show you how to work with it yourself.
                        </p>
                        <p class="faq-text">
                            there is no refunds desk for blaming the framework.<br>
                            there is no complaints box for avoiding responsibility.
                        </p>
                        <p class="faq-text">
                            this is grown up magic 😌✨
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">why the keys are protected 🤍</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            this matters 😘
                        </p>
                        <p class="faq-text">
                            the master magical keys are not just information.<br>
                            they are assigned intelligence.
                        </p>
                        <p class="faq-text">
                            they activate through my voice, sequencing, and timing<br>
                            because that is how they remain clean, safe, and effective.
                        </p>
                        <p class="faq-text">
                            this is not ego.<br>
                            it is how transmission works.
                        </p>
                        <p class="faq-text">
                            they cannot be copied, repackaged, or "explained better" by someone else.<br>
                            they activate only through direct exposure to the source they were approved through.
                        </p>
                        <p class="faq-text">
                            that's why they won't work for<br>
                            people they're not meant for<br>
                            people trying to shortcut power<br>
                            people consuming them out of context
                        </p>
                        <p class="faq-text">
                            and honestly 🤍 good.<br>
                            that's the protection.
                        </p>
                        <p class="faq-text">
                            if they're not for you<br>
                            nothing bad happens.<br>
                            they simply don't activate.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">refund policy 🤍</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            <strong>digital access policy</strong><br>
                            this is a digital experience.<br>
                            once access is delivered, it cannot be returned.
                        </p>
                        <p class="faq-text">
                            purchases are final for:<br>
                            — change of mind<br>
                            — curiosity purchases<br>
                            — not resonating with the material<br>
                            — choosing not to complete the experience
                        </p>
                        <p class="faq-text">
                            under Australian Consumer Law you are entitled to a refund if the product is faulty, not delivered, or cannot be accessed.<br>
                            if this occurs, contact us and we will resolve it.
                        </p>
                        <p class="faq-text">
                            we do not guarantee personal, financial or emotional outcomes.<br>
                            the Key is a self-guided tool.<br>
                            your results depend entirely on your own actions and decisions.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">disclaimer 🤍</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            the Money Key is a guided reflective exercise.
                        </p>
                        <p class="faq-text">
                            it is not:<br>
                            — financial advice<br>
                            — investment advice<br>
                            — therapeutic counselling<br>
                            — psychological treatment<br>
                            — medical advice
                        </p>
                        <p class="faq-text">
                            nothing on this website should be relied upon as professional advice.<br>
                            always seek a qualified professional for financial, legal, medical or mental health decisions.
                        </p>
                        <p class="faq-text">
                            payments are securely processed through PayPal.<br>
                            we do not store or have access to your card details.
                        </p>
                        <p class="faq-text">
                            your email may receive occasional updates.<br>
                            you can unsubscribe at any time using the link in any email.<br>
                            you may request your information be deleted by contacting us.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">privacy statement & terms of use 🤍</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            by purchasing you acknowledge:<br>
                            — this is a digital product<br>
                            — access is delivered immediately<br>
                            — no physical item is shipped<br>
                            — you are purchasing access to content, not guaranteed results<br>
                            — you are responsible for your own decisions and actions
                        </p>
                        <p class="faq-text">
                            sharing, reproducing or distributing the content publicly is not permitted.
                        </p>
                        <p class="faq-text">
                            we collect your name and email to deliver access to your purchase and to communicate updates related to your access.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">need help with your access? 🤍</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            for any questions about your access, please contact us at<br>
                            <strong>support@mastermagicalkey.com</strong>
                        </p>
                        <p class="faq-text">
                            we aim to respond within 2 business days.<br>
                            this inbox is for purchase and access support only.<br>
                            the Master Magical Key is a self-guided digital experience and personalised coaching or advice is not provided via email.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">can I use the Key in my own language?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            yes — you can complete the Key in any language you naturally think in.<br>
                            first listen passively to the original audio in my voice 3 times, then speak the statement in your language 6 times and read it in your language 9 times.<br>
                            the words may be translated, but the audio stays the same and acts as the guide.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">is there a right way to say or read the words?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            no — you do not need perfect pronunciation, reading ability, accent, mood, or environment.<br>
                            you cannot fail or do it wrong.<br>
                            if you stumble, forget words, misread, go slowly, whisper, restart, or feel nothing — that is completely fine.<br>
                            you are simply participating.<br>
                            listen 3 times, speak 6 times, read 9 times.<br>
                            just complete it once and return to your normal day.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">can I play it while driving, working, or multitasking?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            no — you should sit and participate in the speaking and reading portions.<br>
                            it needs your attention for those minutes.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">should I focus hard or try to make something happen?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            no — do not force concentration, visualise, or try to produce a feeling or result.<br>
                            simply listen 3 times, then speak 6 times, then read 9 times at a normal pace and let the experience complete.<br>
                            afterward there is nothing you need to analyse or repeat.<br>
                            drink some water, return to your normal day, and allow your mind to settle naturally.<br>
                            avoid immediately re-doing it, overthinking it, or searching for signs.<br>
                            just leave it finished and carry on with ordinary life.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">should I try to track results or watch for signs?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            no — do not monitor, test, or constantly check for proof.<br>
                            over-watching keeps your attention on the loop instead of closing it.<br>
                            just continue normal life.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">can I change the words?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            you may translate them into your own language, but do not rewrite, add affirmations, or modify the structure.<br>
                            the steps stay the same.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">should I combine it with other methods, rituals, or techniques?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            no — complete it on its own.<br>
                            you don't need to stack it with manifesting practices, tarot, scripting, or additional exercises.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">what if I feel emotional afterward?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            that can happen — just rest, hydrate, and go gently for the day.<br>
                            you don't need to interpret it.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">what if nothing obvious happens?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            that is normal — the process is the completion itself.<br>
                            you are not required to notice a change immediately.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">should I explain it to others while they are doing it?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            let each person experience it directly.<br>
                            avoid coaching or guiding them through it — they only need the instructions.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">is there anything else I need to know before using the Key?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            each access is personal and must be completed by you, not for someone else.<br>
                            you only need a quiet 11–30 minutes where you can hear the audio clearly (headphones optional).<br>
                            if interrupted, simply restart later.
                        </p>
                        <p class="faq-text">
                            it is a one-time access, not a subscription, and you can use it whenever you are ready.<br>
                            you do not need belief, special mood, or perfect reading.
                        </p>
                        <p class="faq-text">
                            do not rewrite, share, screenshot, coach others through it, or combine it with other practices.<br>
                            do not track results, repeat it out of reassurance, or analyse afterward.<br>
                            complete the steps once, rest, hydrate, and return to normal life.<br>
                            feeling emotional, tired, or noticing nothing is all fine — just leave it finished.
                        </p>
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-header">
                    <h3 class="faq-question">what if I have technical issues or can't access it?</h3>
                    <span class="faq-icon"><i class="fas fa-chevron-down"></i></span>
                </div>
                <div class="faq-content">
                    <div class="faq-content-inner">
                        <p class="faq-text">
                            after purchase you receive a downloadable PDF and audio file you can save and use anytime.<br>
                            it works on any normal phone, tablet, or computer — no special apps or software required.<br>
                            you only need internet to download it the first time.
                        </p>
                        <p class="faq-text">
                            if you get interrupted, close the file, or lose your place — simply reopen it later and start again when you have a quiet 11 minutes.<br>
                            you won't break it and nothing is lost.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="faq-section">
        <h2 class="section-title">final agreement 🤍</h2>
        <p class="faq-text">
            you are sovereign.<br>
            you are not being saved.<br>
            you are not being carried.
        </p>
        <p class="faq-text">
            you are being given access.
        </p>
        <p class="faq-text">
            access to intelligence you can activate 🔑<br>
            access to a map you can learn to work with 🗺️<br>
            and in time 🤍 access to advanced tools when your capacity matches them ⚡
        </p>
        <p class="faq-text">
            if this feels<br>
            grounded 🤍 exciting 🤍 calm 🤍<br>
            and a little like "oh… this is real" 😮‍💨
        </p>
        <p class="faq-text">
            you're in the right place.
        </p>
        <p class="faq-text" style="text-align: center; margin-top: 30px; font-size: 1.2rem;">
            <strong>welcome 🤍<br>
            kiss kiss 😘🗝️✨</strong>
        </p>
    </div>
</div>
@endsection