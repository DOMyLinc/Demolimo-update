import { AudioEngine } from './AudioEngine';

document.addEventListener('DOMContentLoaded', async () => {
    const engine = new AudioEngine();

    const btnPlay = document.getElementById('btn-play');
    const btnStop = document.getElementById('btn-stop');
    const btnRecord = document.getElementById('btn-record');
    const btnZen = document.getElementById('btn-zen');

    const workspace = document.getElementById('workspace');
    const mixerPanel = document.getElementById('mixer-panel');
    const canvas = document.getElementById('visualizer-mini');
    const ctx = canvas.getContext('2d');

    // Initialize Audio Context
    let initialized = false;
    const initAudio = async () => {
        if (!initialized) {
            await engine.init();
            initialized = true;
            drawVisualizer();
        }
    };

    // Transport Controls
    btnPlay.addEventListener('click', async () => {
        await initAudio();
        engine.play();
        btnPlay.classList.add('text-green-300', 'bg-white/10');
    });

    btnStop.addEventListener('click', () => {
        engine.stop();
        btnPlay.classList.remove('text-green-300', 'bg-white/10');
    });

    btnRecord.addEventListener('click', async () => {
        await initAudio();
        engine.record();
        btnRecord.classList.toggle('animate-pulse');
    });

    // Zen Mode
    let isZen = false;
    btnZen.addEventListener('click', () => {
        isZen = !isZen;
        if (isZen) {
            workspace.classList.add('opacity-0', 'pointer-events-none');
            mixerPanel.classList.add('translate-y-full');
            btnZen.classList.add('bg-orange-500', 'text-white');
            btnZen.classList.remove('text-orange-400');
        } else {
            workspace.classList.remove('opacity-0', 'pointer-events-none');
            mixerPanel.classList.remove('translate-y-full');
            btnZen.classList.remove('bg-orange-500', 'text-white');
            btnZen.classList.add('text-orange-400');
        }
    });

    // Visualizer Loop
    function drawVisualizer() {
        requestAnimationFrame(drawVisualizer);

        const values = engine.getVisualizerData();
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const barWidth = canvas.width / values.length;
        let x = 0;

        for (let i = 0; i < values.length; i++) {
            const value = values[i]; // -Infinity to 0 dB usually, but FFT gives different range
            const height = (value + 140) * 2; // Normalize roughly

            ctx.fillStyle = `hsl(${i * 5 + 20}, 100%, 50%)`; // Orange/Yellow gradient
            ctx.fillRect(x, canvas.height - height, barWidth - 1, height);

            x += barWidth;
        }
    }
});
