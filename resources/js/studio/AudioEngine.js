import * as Tone from 'tone';

export class AudioEngine {
    constructor() {
        this.isPlaying = false;
        this.transport = Tone.Transport;

        // Create Analyser
        this.analyser = new Tone.Analyser('fft', 64);
        this.master = Tone.Destination;
        this.master.connect(this.analyser);
    }

    async init() {
        await Tone.start();
        console.log('Audio Engine Initialized with Analyser');
    }

    play() {
        if (this.isPlaying) return;
        this.transport.start();
        this.isPlaying = true;
    }

    stop() {
        this.transport.stop();
        this.isPlaying = false;
    }

    record() {
        console.log('Recording...');
    }

    getVisualizerData() {
        return this.analyser.getValue();
    }
}
