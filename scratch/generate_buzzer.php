<?php
/**
 * Generates a buzzer.wav file with 3 beeps
 * Run: php scratch/generate_buzzer.php
 */

$sampleRate = 44100;
$numChannels = 1;
$bitsPerSample = 16;
$amplitude = 16000; // 0–32767

// Build samples: 3 beeps × (0.3s ON + 0.2s OFF)
$samples = [];
$freq = 880; // Hz

$beepDuration   = 0.35 * $sampleRate; // samples
$silenceDuration = 0.20 * $sampleRate; // samples
$numBeeps = 3;

for ($b = 0; $b < $numBeeps; $b++) {
    // ON phase – sine wave
    for ($i = 0; $i < $beepDuration; $i++) {
        $t = $i / $sampleRate;
        // Fade-in / fade-out envelope
        $env = 1.0;
        if ($i < 0.01 * $sampleRate) {
            $env = $i / (0.01 * $sampleRate);
        } elseif ($i > $beepDuration - 0.05 * $sampleRate) {
            $env = ($beepDuration - $i) / (0.05 * $sampleRate);
        }
        $samples[] = (int)($amplitude * $env * sin(2 * M_PI * $freq * $t));
    }
    // OFF phase – silence
    for ($i = 0; $i < $silenceDuration; $i++) {
        $samples[] = 0;
    }
}

$numSamples  = count($samples);
$dataSize    = $numSamples * ($bitsPerSample / 8);
$chunkSize   = 36 + $dataSize;
$byteRate    = $sampleRate * $numChannels * ($bitsPerSample / 8);
$blockAlign  = $numChannels * ($bitsPerSample / 8);

// Build WAV binary
$wav  = 'RIFF';
$wav .= pack('V', $chunkSize);
$wav .= 'WAVE';
$wav .= 'fmt ';
$wav .= pack('V', 16);              // Subchunk1Size
$wav .= pack('v', 1);               // PCM audio format
$wav .= pack('v', $numChannels);
$wav .= pack('V', $sampleRate);
$wav .= pack('V', $byteRate);
$wav .= pack('v', $blockAlign);
$wav .= pack('v', $bitsPerSample);
$wav .= 'data';
$wav .= pack('V', $dataSize);

foreach ($samples as $s) {
    $wav .= pack('v', $s & 0xFFFF);
}

$outPath = __DIR__ . '/../public/sounds/buzzer.wav';
file_put_contents($outPath, $wav);
echo "Generated: $outPath (" . round(filesize($outPath) / 1024, 1) . " KB)\n";
