const AnnoyingSounds = {
    // Generate an ear-piercing alert sound
    generateEmergencyAlert: function() {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const now = audioContext.currentTime;
        
        // Create multiple oscillators for maximum annoyance
        const oscillators = [];
        const frequencies = [880, 1760, 880, 440, 880, 1760];
        
        for (let i = 0; i < frequencies.length; i++) {
            const osc = audioContext.createOscillator();
            const gain = audioContext.createGain();
            osc.connect(gain);
            gain.connect(audioContext.destination);
            osc.frequency.value = frequencies[i];
            gain.gain.value = 0.5;
            oscillators.push({osc, gain});
            
            osc.start();
            gain.gain.exponentialRampToValueAtTime(0.00001, now + 0.5);
            osc.stop(now + 0.5);
        }
        
        return oscillators;
    },
    
    // Play repeating alert sound
    playRepeatingAlert: function(duration = 5000, interval = 1000) {
        let count = 0;
        const maxCount = duration / interval;
        
        const intervalId = setInterval(() => {
            this.generateEmergencyAlert();
            count++;
            if (count >= maxCount) {
                clearInterval(intervalId);
            }
        }, interval);
        
        return intervalId;
    },
    
    // Play siren sound for rider assignment
    playSiren: function() {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const now = audioContext.currentTime;
        
        const osc = audioContext.createOscillator();
        const gain = audioContext.createGain();
        osc.connect(gain);
        gain.connect(audioContext.destination);
        
        osc.frequency.setValueAtTime(440, now);
        osc.frequency.exponentialRampToValueAtTime(880, now + 0.5);
        osc.frequency.exponentialRampToValueAtTime(440, now + 1);
        osc.frequency.exponentialRampToValueAtTime(880, now + 1.5);
        
        gain.gain.setValueAtTime(0.5, now);
        gain.gain.exponentialRampToValueAtTime(0.00001, now + 2);
        
        osc.start();
        osc.stop(now + 2);
    }
};

window.AnnoyingSounds = AnnoyingSounds;