<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Client;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private $openai;
    
    public function __construct()
    {
        $this->openai = \OpenAI::client(config('services.openai.api_key'));
    }

    public function chat(Request $request)
    {
        try {
            $message = $request->input('message');
            $imageContext = $request->input('imageContext', []);
            
            // Build the conversation context
            $systemPrompt = $this->buildSystemPrompt($imageContext);
            
            $response = $this->openai->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            return response()->json([
                'success' => true,
                'message' => $response->choices[0]->message->content
            ]);

        } catch (\Exception $e) {
            Log::error('ChatGPT Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'I apologize, but I encountered an error processing your message. Please try again.'
            ], 500);
        }
    }

    private function buildSystemPrompt($imageContext = [])
    {
        $basePrompt = "You are an AI assistant capable of seeing and analyzing images. ";
        
        if (!empty($imageContext)) {
            $basePrompt .= "In the current image, I can see: \n";
            
            if (!empty($imageContext['labels'])) {
                $labels = collect($imageContext['labels'])
                    ->pluck('description')
                    ->take(5)
                    ->implode(', ');
                $basePrompt .= "- General contents: $labels\n";
            }
            
            if (!empty($imageContext['objects'])) {
                $objects = collect($imageContext['objects'])
                    ->pluck('name')
                    ->take(5)
                    ->implode(', ');
                $basePrompt .= "- Specific objects: $objects\n";
            }
            
            if (!empty($imageContext['text'])) {
                $basePrompt .= "- Text in image: \"{$imageContext['text']}\"\n";
            }
            
            if (isset($imageContext['faces'])) {
                $basePrompt .= "- Number of faces: {$imageContext['faces']}\n";
            }
        }
        
        $basePrompt .= "\nRespond naturally to the user's message, incorporating relevant details about the image when appropriate. 
        Be conversational but informative. If the user asks about specific aspects of the image, use the provided context to give accurate answers.
        If you're not sure about something in the image, be honest about it.";
        
        return $basePrompt;
    }
} 