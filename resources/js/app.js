import './bootstrap';

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => [...document.querySelectorAll(selector)];

const input = $('#videoInput');
const dropZone = $('#dropZone');
const analyzeButton = $('#analyzeButton');
let count = 8;

function setFile(file) {
    if (!file) return;
    $('#fileName').textContent = file.name;
    $('#fileMeta').textContent = `${(file.size / 1024 / 1024).toFixed(1)} MB · Ready to analyze`;
    analyzeButton.disabled = false;
    dropZone.style.borderStyle = 'solid';
}

$('#browseButton').addEventListener('click', () => input.click());
input.addEventListener('change', () => setFile(input.files[0]));
['dragenter', 'dragover'].forEach((event) => dropZone.addEventListener(event, (e) => { e.preventDefault(); dropZone.classList.add('dragging'); }));
['dragleave', 'drop'].forEach((event) => dropZone.addEventListener(event, (e) => { e.preventDefault(); dropZone.classList.remove('dragging'); }));
dropZone.addEventListener('drop', (e) => setFile(e.dataTransfer.files[0]));

$$('.signal').forEach((button) => button.addEventListener('click', () => button.classList.toggle('selected')));
$$('.segmented button').forEach((button) => button.addEventListener('click', () => {
    $$('.segmented button').forEach((item) => item.classList.remove('selected'));
    button.classList.add('selected');
}));

function updateCount(amount) {
    count = Math.min(12, Math.max(3, count + amount));
    $('#countValue').textContent = count;
}
$('#minusCount').addEventListener('click', () => updateCount(-1));
$('#plusCount').addEventListener('click', () => updateCount(1));
$('#captionToggle').addEventListener('click', (event) => {
    event.currentTarget.classList.toggle('on');
    event.currentTarget.setAttribute('aria-checked', event.currentTarget.classList.contains('on'));
});
$('#themeButton').addEventListener('click', () => document.body.classList.toggle('light'));

$('#resetButton').addEventListener('click', () => {
    $$('.signal').forEach((button) => button.classList.add('selected'));
    $$('.segmented button').forEach((button) => button.classList.toggle('selected', button.dataset.length === '60'));
    count = 8; $('#countValue').textContent = count;
    $('#captionToggle').classList.add('on');
});

const clips = [
    { title: 'The knight sacrifice nobody saw', time: '01:42:18', duration: '0:42', score: '96% moment score', caption: 'LA LA… CHOF HAD L-MOVE! 🤯', tags: ['Chess tactic', 'Big reaction'] },
    { title: 'Chat called it — and they were right', time: '00:38:04', duration: '0:31', score: '91% moment score', caption: 'WALLAH 3ENDKOM L-HAQ 😂', tags: ['Chat moment', 'Funny'] },
    { title: 'One move from disaster', time: '02:16:47', duration: '0:55', score: '88% moment score', caption: 'ASH DERT ANA DABA…', tags: ['Blunder', 'Reaction'] },
];

function showResults() {
    $('#clipsGrid').innerHTML = clips.map((clip) => `<article class="clip-card">
        <div class="clip-visual"><span class="clip-score">✦ ${clip.score}</span><button class="play" aria-label="Preview ${clip.title}">▶</button><div class="caption-preview">${clip.caption}</div></div>
        <div class="clip-info"><h3>${clip.title}</h3><p>${clip.time} · ${clip.duration}</p><div class="clip-meta">${clip.tags.map((tag) => `<span>${tag}</span>`).join('')}</div></div>
        <button class="export-button">Export vertical clip ↗</button>
    </article>`).join('');
    $('#results').hidden = false;
    $$('.export-button').forEach((button) => button.addEventListener('click', showToast));
    $('#results').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

analyzeButton.addEventListener('click', () => {
    analyzeButton.hidden = true;
    $('#progressWrap').hidden = false;
    let progress = 0;
    const timer = setInterval(() => {
        progress += 4;
        $('#progressPercent').textContent = `${progress}%`;
        $('#progressBar').style.width = `${progress}%`;
        if (progress === 40) $('#progressLabel').textContent = 'Transcribing Darija locally…';
        if (progress === 72) $('#progressLabel').textContent = 'Ranking the strongest moments…';
        if (progress >= 100) { clearInterval(timer); showResults(); analyzeButton.hidden = false; analyzeButton.textContent = 'Analyze again →'; $('#progressWrap').hidden = true; }
    }, 45);
});

function showToast() {
    $('#toast').classList.add('show');
    setTimeout(() => $('#toast').classList.remove('show'), 2800);
}

$('#newStream').addEventListener('click', () => { input.value = ''; location.reload(); });
