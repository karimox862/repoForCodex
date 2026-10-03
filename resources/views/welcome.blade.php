<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071411">
    <title>ClipDarija — turn streams into clips</title>
    <meta name="description" content="A free, local-first clip finder built for Darija streamers.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="ambient ambient-one"></div>
    <div class="ambient ambient-two"></div>

    <header class="site-header">
        <a class="brand" href="#" aria-label="ClipDarija home">
            <span class="brand-mark"><span></span><span></span><span></span></span>
            <span>clip<span>darija</span></span>
        </a>
        <nav aria-label="Main navigation">
            <a class="active" href="#workspace">Workspace</a>
            <a href="#how-it-works">How it works</a>
        </nav>
        <div class="header-actions">
            <span class="local-pill"><i></i> Runs locally</span>
            <button class="icon-button" id="themeButton" aria-label="Toggle theme">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v2m0 14v2M3 12h2m14 0h2M5.64 5.64l1.42 1.42m9.88 9.88 1.42 1.42m0-12.72-1.42 1.42M7.06 16.94l-1.42 1.42M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
            </button>
        </div>
    </header>

    <main>
        <section class="hero" id="workspace">
            <div class="eyebrow"><span>✦</span> Built for Moroccan creators</div>
            <h1>Your best moments,<br><em>ma ydi3o-sh.</em></h1>
            <p>Drop your stream. ClipDarija finds the reactions, blunders and wins worth sharing — with Darija-aware captions, right on your computer.</p>
            <div class="trust-row">
                <span><b>∞</b> No minute limits</span>
                <span><b>⌁</b> No uploads</span>
                <span><b>0<span>DH</span></b> Forever free</span>
            </div>
        </section>

        <section class="workbench" aria-label="Clip creation workspace">
            <div class="upload-card" id="dropZone">
                <input type="file" id="videoInput" accept="video/*" hidden>
                <div class="upload-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/></svg>
                </div>
                <div>
                    <h2 id="fileName">Drop your stream recording</h2>
                    <p id="fileMeta">MP4, MOV or MKV · up to 6 hours</p>
                </div>
                <button class="browse-button" id="browseButton">Choose a file</button>
                <small>Your video never leaves this device.</small>
            </div>

            <div class="settings-card">
                <div class="card-heading">
                    <div><span>02</span><div><h2>Tell us what to find</h2><p>Fine-tune your highlights</p></div></div>
                    <button class="reset-button" id="resetButton">Reset</button>
                </div>

                <div class="setting-block">
                    <label>Content signals</label>
                    <div class="signal-grid">
                        <button class="signal selected" data-signal="reactions"><span class="signal-icon">⚡</span><b>Big reactions</b><small>Laughs, shock, hype</small><i>✓</i></button>
                        <button class="signal selected" data-signal="chess"><span class="signal-icon">♞</span><b>Chess moments</b><small>Blunders, tactics, wins</small><i>✓</i></button>
                        <button class="signal selected" data-signal="chat"><span class="signal-icon">◌</span><b>Chat moments</b><small>Stories & banter</small><i>✓</i></button>
                    </div>
                </div>

                <div class="setting-row">
                    <div><label for="clipLength">Clip length</label><p>Maximum duration per clip</p></div>
                    <div class="segmented" id="clipLength">
                        <button data-length="30">30s</button><button class="selected" data-length="60">60s</button><button data-length="90">90s</button>
                    </div>
                </div>
                <div class="setting-row">
                    <div><label for="clipCount">Number of clips</label><p>How many candidates to find</p></div>
                    <div class="stepper" id="clipCount"><button id="minusCount">−</button><b id="countValue">8</b><button id="plusCount">+</button></div>
                </div>
                <div class="setting-row last">
                    <div><label for="captionToggle">Darija captions</label><p>Auto-generate burned-in subtitles</p></div>
                    <button class="toggle on" id="captionToggle" role="switch" aria-checked="true"><span></span></button>
                </div>

                <button class="analyze-button" id="analyzeButton" disabled><span>Find my best clips</span><b>→</b></button>
                <div class="progress-wrap" id="progressWrap" hidden><div><span id="progressLabel">Listening for the good parts…</span><b id="progressPercent">0%</b></div><div class="progress-track"><i id="progressBar"></i></div></div>
            </div>
        </section>

        <section class="results" id="results" hidden>
            <div class="results-heading"><div><span class="eyebrow"><span>✦</span> Analysis complete</span><h2>Your clip shortlist</h2><p>Preview, refine and export the moments you want to share.</p></div><button class="secondary-button" id="newStream">＋ New stream</button></div>
            <div class="clips-grid" id="clipsGrid"></div>
        </section>

        <section class="how" id="how-it-works">
            <p class="section-kicker">LOCAL-FIRST, BY DESIGN</p>
            <h2>From long stream to short-form,<br>in three quiet steps.</h2>
            <div class="steps">
                <article><span>01</span><div class="step-icon">▣</div><h3>Add your recording</h3><p>Pick the OBS recording already on your computer. Nothing gets uploaded.</p></article>
                <article><span>02</span><div class="step-icon">⌁</div><h3>We find the energy</h3><p>Darija speech, audio peaks and chess cues reveal the moments that matter.</p></article>
                <article><span>03</span><div class="step-icon">↗</div><h3>Polish & post</h3><p>Choose a highlight, adjust the cut, then export it ready for socials.</p></article>
            </div>
        </section>
    </main>

    <footer><a class="brand" href="#"><span class="brand-mark"><span></span><span></span><span></span></span><span>clip<span>darija</span></span></a><p>Made for streamers who speak <b>from the heart.</b></p><span>Private · Local · Free</span></footer>

    <div class="toast" id="toast" role="status">Export started — your clip will be ready shortly.</div>
</body>
</html>
