<?php
/**
 * AI Chatbot API with Google Gemini Integration
 * NexMart E-Commerce
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/gemini-config.php';

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$conversationId = $input['conversationId'] ?? session_id();

// Validate input
if (empty($message)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a message'
    ]);
    exit;
}

// Process the message with Gemini AI
try {
    $response = processWithGemini($message, $conversationId);
    
    echo json_encode([
        'success' => true,
        'response' => $response['text'],
        'suggestions' => $response['suggestions'] ?? [],
        'conversationId' => $conversationId
    ]);
} catch (Exception $e) {
    error_log('Chatbot error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => true,
        'response' => getFallbackResponse($message),
        'suggestions' => ['Show me products', 'Contact us', 'Help'],
        'conversationId' => $conversationId
    ]);
}

/**
 * Process message with Google Gemini AI
 */
function processWithGemini($message, $conversationId) {
    global $pdo;
    
    // Store user message
    storeMessage($conversationId, 'user', $message);
    
    // Get conversation history (last 10 messages for context)
    $history = getConversationHistory($conversationId, 10);
    
    // Get product context if relevant
    $productContext = getProductContext($message);
    
    // Call Gemini API
    $geminiResponse = callGeminiAPI($message, $history, $productContext);
    
    // Store bot response
    storeMessage($conversationId, 'bot', $geminiResponse);
    
    // Generate smart suggestions based on response
    $suggestions = generateSmartSuggestions($geminiResponse, $message);
    
    return [
        'text' => $geminiResponse,
        'suggestions' => $suggestions
    ];
}

/**
 * Call Google Gemini API
 */
function callGeminiAPI($message, $history = [], $productContext = '') {
    $apiKey = GEMINI_API_KEY;
    $apiUrl = GEMINI_API_URL . '?key=' . $apiKey;
    
    // Build conversation context
    $systemPrompt = NEXMART_SYSTEM_PROMPT;
    
    if (!empty($productContext)) {
        $systemPrompt .= "\n\nCURRENT PRODUCT CONTEXT:\n" . $productContext;
    }
    
    // Build the prompt with history
    $fullPrompt = $systemPrompt . "\n\n";
    
    if (!empty($history)) {
        $fullPrompt .= "CONVERSATION HISTORY:\n";
        foreach ($history as $msg) {
            $role = $msg['sender'] === 'user' ? 'Customer' : 'Assistant';
            $fullPrompt .= "$role: " . $msg['message'] . "\n";
        }
        $fullPrompt .= "\n";
    }
    
    $fullPrompt .= "Customer: " . $message . "\nAssistant:";
    
    // Prepare API request
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $fullPrompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => 1024,
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_HARASSMENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_HATE_SPEECH',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ],
            [
                'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
            ]
        ]
    ];
    
    // Make API call
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error || $httpCode !== 200) {
        error_log("Gemini API Error: HTTP $httpCode - $error - Response: $response");
        throw new Exception("API request failed");
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return trim($result['candidates'][0]['content']['parts'][0]['text']);
    }
    
    throw new Exception("Invalid API response");
}

/**
 * Get product context for better AI responses
 */
function getProductContext($message) {
    global $pdo;
    
    $context = '';
    $message = strtolower($message);
    
    // Check if user is asking about specific products
    if (preg_match('/(product|phone|laptop|tablet|watch|gaming|headphone|accessory|iphone|samsung|macbook|ipad)/i', $message)) {
        try {
            // Get featured or popular products
            $stmt = $pdo->query("
                SELECT name, category, price, stock 
                FROM products 
                WHERE status = 'active' 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($products) {
                $context = "Here are some of our current products:\n";
                foreach ($products as $product) {
                    $context .= "- {$product['name']} ({$product['category']}) - \${$product['price']} - " . 
                               ($product['stock'] > 0 ? "In Stock" : "Out of Stock") . "\n";
                }
            }
        } catch (Exception $e) {
            error_log("Error fetching product context: " . $e->getMessage());
        }
    }
    
    // Get categories
    if (preg_match('/(category|categories|what do you sell)/i', $message)) {
        try {
            $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE status = 'active'");
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if ($categories) {
                $context .= "\nOur product categories: " . implode(', ', $categories);
            }
        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
        }
    }
    
    return $context;
}

/**
 * Generate smart suggestions based on AI response
 */
function generateSmartSuggestions($response, $userMessage) {
    $suggestions = [];
    $response = strtolower($response);
    $userMessage = strtolower($userMessage);
    
    // Context-aware suggestions
    if (strpos($response, 'product') !== false || strpos($userMessage, 'product') !== false) {
        $suggestions[] = 'Show me all products';
        $suggestions[] = 'What categories do you have?';
    }
    
    if (strpos($response, 'order') !== false || strpos($userMessage, 'order') !== false) {
        $suggestions[] = 'Track my order';
        $suggestions[] = 'How do I place an order?';
    }
    
    if (strpos($response, 'price') !== false || strpos($userMessage, 'price') !== false) {
        $suggestions[] = 'Show me best deals';
        $suggestions[] = 'Do you have discounts?';
    }
    
    if (strpos($response, 'shipping') !== false || strpos($userMessage, 'delivery') !== false) {
        $suggestions[] = 'Shipping policy';
        $suggestions[] = 'How long is delivery?';
    }
    
    // Default suggestions if none matched
    if (empty($suggestions)) {
        $suggestions = [
            'Show me products',
            'Contact information',
            'Tell me about NexMart'
        ];
    }
    
    return array_slice($suggestions, 0, 3); // Return max 3 suggestions
}

/**
 * Get conversation history
 */
function getConversationHistory($conversationId, $limit = 10) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT sender, message, created_at 
            FROM chat_conversations 
            WHERE conversation_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$conversationId, $limit]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_reverse($messages); // Return in chronological order
    } catch (Exception $e) {
        error_log("Error fetching conversation history: " . $e->getMessage());
        return [];
    }
}

/**
 * Store message in database
 */
function storeMessage($conversationId, $sender, $message) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO chat_conversations (conversation_id, sender, message, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$conversationId, $sender, $message]);
    } catch (Exception $e) {
        error_log("Error storing message: " . $e->getMessage());
    }
}

/**
 * Fallback response when Gemini API fails
 */
function getFallbackResponse($message) {
    $message = strtolower(trim($message));
    
    // Simple pattern matching for fallback
    if (preg_match('/^(hi|hello|hey)/i', $message)) {
        return "Hello! 👋 Welcome to NexMart! I'm here to help you find the perfect electronics. What are you looking for today?";
    }
    
    if (preg_match('/(product|show|find)/i', $message)) {
        return "I'd love to help you find products! Please visit our products page to browse our latest smartphones, laptops, tablets, and more. Is there a specific category you're interested in?";
    }
    
    if (preg_match('/(contact|phone|email|address)/i', $message)) {
        return "You can reach us at:\n📞 (+95) 9 771 662558\n📧 saikyawthihacs@gmail.com\n📍 123, Pyay Road, Kamayut, Yangon\nWe're open Mon-Fri, 9 AM - 6 PM";
    }
    
    if (preg_match('/(order|track|delivery)/i', $message)) {
        return "To track your order, please log in to your account and visit the Orders page. You'll see all your order details and tracking information there. Need help with anything else?";
    }
    
    return "I'm here to help! You can ask me about our products, orders, shipping, returns, or anything else about NexMart. What would you like to know?";
}
