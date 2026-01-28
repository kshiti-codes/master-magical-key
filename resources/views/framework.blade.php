@extends('layouts.app')

@push('styles')
<style>
    .map-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
        position: relative;
        z-index: 1;
    }
    
    .map-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        text-align: center;
        font-size: 2.2rem;
        margin-bottom: 30px;
        letter-spacing: 3px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .map-section {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 40px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 30px rgba(138, 43, 226, 0.3);
        backdrop-filter: blur(10px);
    }
    
    .section-subtitle {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.3rem;
        letter-spacing: 2px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .map-text {
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.8;
        margin-bottom: 20px;
        font-size: 1.05rem;
    }
    
    .map-text.center {
        text-align: center;
    }
    
    .map-text.italic {
        font-style: italic;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .highlight-text {
        color: #d8b5ff;
        font-weight: 500;
    }
    
    .emoji-text {
        display: inline-block;
        margin: 0 3px;
    }
    
    .divider {
        height: 1px;
        background: linear-gradient(to right, transparent, rgba(138, 43, 226, 0.5), transparent);
        margin: 30px 0;
    }
    
    .key-symbol {
        text-align: center;
        font-size: 2rem;
        margin: 20px 0;
    }
    
    .signature {
        text-align: center;
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        font-size: 1.2rem;
        margin-top: 40px;
        letter-spacing: 1px;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 768px) {
        .map-container {
            padding: 20px 15px;
        }
        
        .map-title {
            font-size: 1.8rem;
        }
        
        .map-section {
            padding: 20px;
        }
        
        .map-text {
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="map-container">
    <h1 class="map-title">THE FRAMEWORK</h1>
    <p class="section-subtitle">(About the work)</p>
    
    <div class="map-section">
        <p class="map-text center">
            <span class="highlight-text">welcome</span> <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text center">
            let me explain this properly
        </p>
        
        <p class="map-text">
            not like a textbook <span class="emoji-text">🤢</span> not like a spiritual lecture <span class="emoji-text">😴</span> and definitely not like something that wants you to heal for ten years before you're "allowed" to receive <span class="emoji-text">😮‍💨</span>
        </p>
        
        <p class="map-text">
            we are not here to spiritually exfoliate ourselves into dust. we are not here to pray harder, vibe higher, or collapse our nervous system in the name of growth.
        </p>
        
        <p class="map-text">
            we want magic now <span class="emoji-text">🪄</span> we want results now <span class="emoji-text">💸</span> we want things to work without losing our sanity, safety, or pleasure in the process <span class="emoji-text">😘</span>
        </p>
        
        <p class="map-text">
            and yes… before you ask <span class="emoji-text">😏</span> i do have the secret. past the secret. past the fluffy manifestation stuff that keeps people circling instead of actually arriving.
        </p>
        
        <p class="map-text">
            it comes with a 369 key built on the same intelligence that helped electrify the world <span class="emoji-text">⚡️</span> actual electricity. lights on. civilisation shifting creation.
        </p>
        
        <p class="map-text">
            so yeah… the maths is mathin <span class="emoji-text">🤍</span> and if this intelligence can pull power out of thin air and light up an entire planet…
        </p>
        
        <p class="map-text">
            imagine what happens when it's applied to money <span class="emoji-text">💸</span> love <span class="emoji-text">❤️</span> timing <span class="emoji-text">⏳</span> safety <span class="emoji-text">🛡️</span> confidence <span class="emoji-text">👑</span> and your actual real life
        </p>
        
        <p class="map-text center">
            right <span class="emoji-text">😮‍💨🔥</span>
        </p>
        
        <p class="map-text">
            if your brain is suspicious… good <span class="emoji-text">😏🧠</span>
        </p>
        
        <p class="map-text">
            bring the doubt. bring the curiosity. bring the "who does she think she is?" energy.
        </p>
        
        <p class="map-text">
            don't suppress it. clock it. log it.
        </p>
        
        <p class="map-text">
            because that reaction is intelligence waking up and you are completely safe here while it does <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text">
            we'll take all of that information straight to the map later and cash it in <span class="emoji-text">😘✨</span>
        </p>
        
        <div class="divider"></div>
        
        <p class="section-subtitle">what this actually is 🤍</p>
        
        <p class="map-text">
            the master magical key is not a belief system. it's not a religion. it's not a lifestyle brand.
        </p>
        
        <p class="map-text">
            it's an intelligence system built on the literal laws of creation.
        </p>
        
        <p class="map-text">
            sunrise <span class="emoji-text">🌅</span> seasons <span class="emoji-text">🍂</span> birth <span class="emoji-text">👶</span> timing <span class="emoji-text">⏳</span> cause → effect → consequence
        </p>
        
        <p class="map-text">
            no vision board required <span class="emoji-text">🐷✨</span>
        </p>
        
        <p class="map-text">
            this system does not care if you believe. it does not require you to be calm, healed, high vibe, or convinced.
        </p>
        
        <p class="map-text">
            it responds to order.
        </p>
        
        <p class="map-text">
            law works whether you believe in it or not. that's why this works <span class="emoji-text">😮‍💨⚙️</span>
        </p>
        
        <div class="divider"></div>
        
        <p class="section-subtitle">how the master magical key works 🤍</p>
        
        <p class="map-text center">
            one system <span class="emoji-text">🤍</span> three levels <span class="emoji-text">🤍</span> infinite applications
        </p>
        
        <p class="map-text">
            this is not random. this is not chaotic. this is layered intelligence.
        </p>
        
        <p class="section-subtitle">✨ LEVEL 1 🤍 THE MASTER MAGICAL KEYS</p>
        
        <p class="map-text center">
            <span class="highlight-text">369 intelligence activation</span> <span class="emoji-text">🔑</span>
        </p>
        
        <p class="map-text">
            this is where intelligence enters you.
        </p>
        
        <p class="map-text">
            not teaching. not effort. not mindset work.
        </p>
        
        <p class="map-text">
            calibration.
        </p>
        
        <p class="map-text">
            each master magical key is a pre designed intelligence transmission that you activate using the 3·6·9 method:
        </p>
        
        <p class="map-text">
            <span class="emoji-text">🎧</span> listen 3 times <span class="emoji-text">🤍</span> passive force<br>
            <span class="emoji-text">🗣️</span> speak 6 times <span class="emoji-text">🤍</span> active force<br>
            <span class="emoji-text">📖</span> read 9 times <span class="emoji-text">🤍</span> neutralising force
        </p>
        
        <p class="map-text">
            that's it.
        </p>
        
        <p class="map-text">
            you are not being hypnotised. you are not being overridden. you are not being controlled.
        </p>
        
        <p class="map-text">
            you are accessing intelligence that already exists inside your superconscious mind <span class="emoji-text">🧠✨</span>
        </p>
        
        <p class="map-text">
            once activated that intelligence begins quietly organising your perception your decisions your timing your behaviour
        </p>
        
        <p class="map-text">
            so reality can respond cleanly <span class="emoji-text">🤍</span> in the right order <span class="emoji-text">🤍</span> at the right moment <span class="emoji-text">🤍</span> with the right amount of pressure <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text">
            things start to move not because you're forcing outcomes but because the system finally has clear instructions correct sequencing and enough safety to hold what's arriving <span class="emoji-text">😮‍💨✨</span>
        </p>
        
        <p class="map-text">
            this is why people see money land and stay <span class="emoji-text">💸</span> relationships stabilise or resolve cleanly <span class="emoji-text">❤️</span> confidence replace anxiety <span class="emoji-text">🧠</span> clarity replace confusion <span class="emoji-text">🔍</span> momentum return <span class="emoji-text">🚀</span> life stop pushing back <span class="emoji-text">🌊</span>
        </p>
        
        <p class="map-text">
            not because they tried harder but because everything finally came into alignment <span class="emoji-text">🧬</span>
        </p>
        
        <p class="map-text">
            this level is perfect for beginners skeptics overwhelmed nervous systems people who "can't do the work" people who want results without self abandonment
        </p>
        
        <p class="map-text center">
            this is how intelligence gets inside you <span class="emoji-text">🔑</span>
        </p>
        
        <div class="divider"></div>
        
        <p class="section-subtitle">🗺️ LEVEL 2 🤍 THE PATH – COMING SOON</p>
        
        <p class="map-text center">
            <span class="highlight-text">walking the map</span> <span class="emoji-text">🤍</span> embodied integration
        </p>
        
        <p class="map-text">
            this is where intelligence becomes lived.
        </p>
        
        <p class="map-text">
            this is not future design. this is not manifestation. this is not skipping ahead.
        </p>
        
        <p class="map-text">
            this is real life integration.
        </p>
        
        <p class="map-text">
            you move through the 9 portals linearly cause → effect → consequence experience → learning → embodiment
        </p>
        
        <p class="map-text">
            this is where insight becomes wisdom safety builds in the body patterns actually change and intelligence settles into behaviour <span class="emoji-text">😮‍💨🤍</span>
        </p>
        
        <p class="map-text">
            you don't edit reality here. you let reality edit you.
        </p>
        
        <p class="map-text">
            life becomes the teacher. and you start trusting it again <span class="emoji-text">🌊✨</span>
        </p>
        
        <p class="map-text">
            this level is for people integrating big change healing journeys stabilising after breakthroughs building safety before expansion
        </p>
        
        <p class="map-text center">
            this is where intelligence becomes embodied <span class="emoji-text">🗺️</span>
        </p>
        
        <div class="divider"></div>
        
        <p class="section-subtitle">⚡ LEVEL 3 🤍 THE INTELLIGENCE GRID – COMING SOON</p>
        
        <p class="map-text center">
            <span class="highlight-text">advanced magic</span> <span class="emoji-text">🤍</span> advanced intelligence <span class="emoji-text">🤍</span> timeline architecture
        </p>
        
        <p class="map-text">
            this is the advanced level <span class="emoji-text">😘🔥</span> this is not beginner magic. this is not for bypassing. this is not for fantasy.
        </p>
        
        <p class="map-text">
            this is for people who can hold power responsibly <span class="emoji-text">👑</span>
        </p>
        
        <p class="map-text">
            here you interact with the portals non linearly future → present desire → structure vision → configuration
        </p>
        
        <p class="map-text">
            this is where you design money trajectories <span class="emoji-text">💸</span> love and relationship timelines <span class="emoji-text">❤️</span> identity shifts <span class="emoji-text">👑</span> business and leadership expansion <span class="emoji-text">⚡</span>
        </p>
        
        <p class="map-text">
            this is not manifestation. this is configuration.
        </p>
        
        <p class="map-text">
            you are not asking reality. you are working with it.
        </p>
        
        <p class="map-text">
            this level is for regulated nervous systems people who have walked the path founders <span class="emoji-text">🤍</span> leaders <span class="emoji-text">🤍</span> creatives those ready to take responsibility for what they create
        </p>
        
        <p class="map-text center">
            this is how intelligence designs reality <span class="emoji-text">⚙️✨</span>
        </p>
        
        <div class="divider"></div>
        
        <p class="section-subtitle">how it all flows 🤍</p>
        
        <p class="map-text">
            this framework isn't hierarchical. there's no "better" or "worse" level. nothing to rush. nothing to graduate from <span class="emoji-text">😌</span>
        </p>
        
        <p class="map-text">
            it's stacked, not ranked <span class="emoji-text">🤍</span> each level supports the others. each layer builds capacity for the next.
        </p>
        
        <p class="map-text center">
            the natural flow looks like this <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text center">
            the keys → the path → the grid
        </p>
        
        <p class="map-text">
            first intelligence enters your system. then life trains you to hold it. and when you're ready you learn how to work with it consciously.
        </p>
        
        <p class="map-text">
            but this isn't a straight line. it's a living system <span class="emoji-text">♾️</span>
        </p>
        
        <p class="map-text">
            you might use the grid to design a shift and be guided back to the path to integrate it <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text">
            you might walk the path for a while and feel called to activate a new key for recalibration <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text">
            new keys deepen your experience on the path. the path stabilises and cleans your use of the grid. the grid reveals what the path is preparing you for.
        </p>
        
        <p class="map-text">
            this looping is intentional <span class="emoji-text">🤍</span> it's what keeps the system alive <span class="emoji-text">🤍</span> adaptive <span class="emoji-text">🤍</span> and safe <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text">
            nothing here forces you forward. nothing here leaves you behind.
        </p>
        
        <div class="divider"></div>
        
        <p class="section-subtitle">the simplest way to understand it 🤍</p>
        
        <p class="map-text">
            the master magical key is an intelligence system.
        </p>
        
        <p class="map-text">
            intelligence enters you <span class="emoji-text">🤍</span> that's the keys <span class="emoji-text">🔑</span><br>
            life trains you <span class="emoji-text">🤍</span> that's the path <span class="emoji-text">🗺️</span><br>
            and when you're ready <span class="emoji-text">🤍</span> you design reality <span class="emoji-text">🤍</span> that's the grid <span class="emoji-text">⚡</span>
        </p>
        
        <p class="map-text">
            no confusion. no bypassing. no pressure.
        </p>
        
        <p class="map-text center">
            just intelligence… moving in the right order <span class="emoji-text">🤍</span>
        </p>
        
        <div class="divider"></div>
        
        <p class="section-subtitle">one last thing 🤍</p>
        
        <p class="map-text">
            i don't give you a method. i don't tell you what to believe. and i don't run your life for you <span class="emoji-text">😘</span>
        </p>
        
        <p class="map-text">
            what i do give you is intelligence you can activate <span class="emoji-text">🔑✨</span> and i'll show you exactly how to do that safely.
        </p>
        
        <p class="map-text">
            i also give you the map <span class="emoji-text">🗺️✨</span> so you can see where you are what's actually happening and what works next <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text">
            and yes… when you're ready <span class="emoji-text">😘</span> i can even show you how to work with the map yourself not blindly not dependently but intelligently <span class="emoji-text">👑</span>
        </p>
        
        <p class="map-text">
            the map doesn't force outcomes. it doesn't override your will. it doesn't bypass your nervous system or make promises it can't keep.
        </p>
        
        <p class="map-text">
            it works with law, not effort. with order, not chaos. with intelligence, not pressure.
        </p>
        
        <p class="map-text">
            what you do with all of that is entirely on you. that's the power. and that's the safety <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text">
            need cash <span class="emoji-text">💸</span> you don't chase it… you activate intelligence and go to the map
        </p>
        
        <p class="map-text">
            need love <span class="emoji-text">❤️</span> you don't force it… you activate intelligence and go to the map
        </p>
        
        <p class="map-text">
            need clarity momentum or a full life detonation and rebuild <span class="emoji-text">🧨</span> yep… intelligence first <span class="emoji-text">🤍</span> map second <span class="emoji-text">😘</span>
        </p>
        
        <p class="map-text">
            that's why this work is powerful. and that's why it's packaged safely <span class="emoji-text">🤍</span>
        </p>
        
        <p class="map-text">
            you get to be exactly who you are here. you get to want what you want here. you get to desire without apologising <span class="emoji-text">😌🔥</span>
        </p>
        
        <p class="map-text center">
            get rich <span class="emoji-text">💸</span> get sexy <span class="emoji-text">😘</span> express yourself perfectly <span class="emoji-text">👑</span> experience deep conscious love <span class="emoji-text">❤️‍🔥</span>
        </p>
        
        <p class="map-text center">
            <span class="highlight-text">welcome</span> <span class="emoji-text">🤍</span><br>
            kiss kiss <span class="emoji-text">😘😘</span>
        </p>
        
        <div class="signature">
            Chanell xxxx
        </div>
    </div>
</div>
@endsection