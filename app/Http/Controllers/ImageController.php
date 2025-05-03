<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature\Type;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            try {
                // 1. Store the image
                $path = $request->file('image')->store('uploads', 'public');
                $fullPath = Storage::path('public/' . $path);

                // 2. Analyze the image using Google Cloud Vision
                $imageAnnotator = new ImageAnnotatorClient([
                    'credentials' => storage_path('app/google-credentials.json')
                ]);

                // Read image content
                $imageContent = file_get_contents($fullPath);

                // Perform multiple types of analysis
                $image = $imageAnnotator->image($imageContent, [
                    'LABEL_DETECTION',
                    'TEXT_DETECTION',
                    'FACE_DETECTION',
                    'OBJECT_LOCALIZATION',
                    'IMAGE_PROPERTIES'
                ]);

                $response = [
                    'labels' => [],
                    'text' => [],
                    'faces' => 0,
                    'objects' => [],
                    'colors' => []
                ];

                // Get labels (general image classification)
                $labels = $imageAnnotator->labelDetection($image);
                foreach ($labels->getLabelAnnotations() as $label) {
                    $response['labels'][] = [
                        'description' => $label->getDescription(),
                        'confidence' => round($label->getScore() * 100, 2)
                    ];
                }

                // Get text from image
                $text = $imageAnnotator->textDetection($image);
                if ($text && $text->getText()) {
                    $response['text'] = $text->getText();
                }

                // Get face detection results
                $faces = $imageAnnotator->faceDetection($image);
                $response['faces'] = count($faces->getFaceAnnotations());

                // Get object detection results
                $objects = $imageAnnotator->objectLocalization($image);
                foreach ($objects->getLocalizedObjectAnnotations() as $object) {
                    $response['objects'][] = [
                        'name' => $object->getName(),
                        'confidence' => round($object->getScore() * 100, 2)
                    ];
                }

                // Get dominant colors
                $properties = $imageAnnotator->imageProperties($image);
                $colors = $properties->getImagePropertiesAnnotation()->getDominantColors()->getColors();
                foreach (array_slice($colors, 0, 5) as $color) {
                    $rgb = $color->getColor();
                    $response['colors'][] = [
                        'red' => $rgb->getRed(),
                        'green' => $rgb->getGreen(),
                        'blue' => $rgb->getBlue(),
                        'score' => round($color->getScore() * 100, 2)
                    ];
                }

                $imageAnnotator->close();

                // 3. Generate a natural language response
                $message = $this->generateNaturalResponse($response);

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'details' => $response,
                    'path' => Storage::url($path)
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error analyzing image: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No image was uploaded.'
        ], 400);
    }

    private function generateNaturalResponse($analysis)
    {
        $response = "Here's what I can see in this image:\n\n";

        // Add label descriptions
        if (!empty($analysis['labels'])) {
            $mainLabels = array_slice($analysis['labels'], 0, 3);
            $labelDescriptions = array_column($mainLabels, 'description');
            $response .= "This appears to be " . implode(", ", $labelDescriptions) . ".\n\n";
        }

        // Add object detection
        if (!empty($analysis['objects'])) {
            $objects = array_slice($analysis['objects'], 0, 3);
            $objectNames = array_column($objects, 'name');
            $response .= "I can identify: " . implode(", ", $objectNames) . ".\n\n";
        }

        // Add face detection
        if ($analysis['faces'] > 0) {
            $response .= "I can see {$analysis['faces']} " . 
                        ($analysis['faces'] == 1 ? "face" : "faces") . " in the image.\n\n";
        }

        // Add text detection
        if (!empty($analysis['text'])) {
            $response .= "I can read the following text: \"{$analysis['text']}\"\n\n";
        }

        // Add color analysis
        if (!empty($analysis['colors'])) {
            $response .= "The dominant colors in this image include ";
            $colorDescriptions = [];
            foreach (array_slice($analysis['colors'], 0, 3) as $color) {
                $colorDescriptions[] = $this->getColorName($color['red'], $color['green'], $color['blue']);
            }
            $response .= implode(", ", $colorDescriptions) . ".\n";
        }

        return $response;
    }

    private function getColorName($r, $g, $b)
    {
        // Basic color naming logic
        if ($r < 30 && $g < 30 && $b < 30) return "black";
        if ($r > 225 && $g > 225 && $b > 225) return "white";
        
        $max = max($r, $g, $b);
        if ($max == $r) return "red";
        if ($max == $g) return "green";
        if ($max == $b) return "blue";
        
        return "mixed";
    }
} 