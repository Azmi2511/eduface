import * as faceapi from '@vladmandic/face-api';
import { Canvas, Image, ImageData, loadImage } from 'canvas';
import path from 'path';
import { fileURLToPath } from 'url';

// Monkey patch for nodejs
faceapi.env.monkeyPatch({ Canvas, Image, ImageData });

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const MODEL_URL = path.join(__dirname, 'node_modules', '@vladmandic', 'face-api', 'model');

async function main() {
    const imagePath = process.argv[2];
    if (!imagePath) {
        console.error(JSON.stringify({ error: 'No image path provided' }));
        process.exit(1);
    }

    try {
        await faceapi.nets.ssdMobilenetv1.loadFromDisk(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromDisk(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromDisk(MODEL_URL);

        const img = await loadImage(imagePath);
        
        const detections = await faceapi.detectSingleFace(img)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detections) {
            console.error(JSON.stringify({ error: 'No face detected' }));
            process.exit(1);
        }

        // Return the descriptor as JSON array
        console.log(JSON.stringify({
            success: true,
            descriptor: Array.from(detections.descriptor)
        }));

    } catch (e) {
        console.error(JSON.stringify({ error: e.message }));
        process.exit(1);
    }
}

main();
