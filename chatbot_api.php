<?php
/**
 * chatbot_api.php
 * ---------------------------------------------------------
 * AJAX endpoint for the Coffee Assistant chatbot.
 * Receives:  POST JSON { "message": "user text" }
 * Returns:   JSON { "reply": "...", "type": "text|menu", "items": [...] }
 * ---------------------------------------------------------
 */

header('Content-Type: application/json; charset=utf-8');
require_once 'config/db.php';
$pdo = $conn;

// ---- Read the incoming JSON body ----
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$userMessage = isset($data['message']) ? trim($data['message']) : '';

if ($userMessage === '') {
    echo json_encode([
        'reply' => "I didn't quite catch that — could you type your question again?",
        'type'  => 'text',
    ]);
    exit;
}

$messageLower = strtolower($userMessage);

/**
 * Helper: fetch popular / matching menu items dynamically from MySQL
 * so the chatbot's menu answers are always in sync with the real database.
 */
function fetchMenuItems(PDO $pdo, ?string $category = null, int $limit = 5): array
{
    if ($category) {
        $stmt = $pdo->prepare("SELECT name, price, category FROM products WHERE category = :cat ORDER BY is_popular DESC, name ASC LIMIT :lim");
        $stmt->bindValue(':cat', $category, PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    } else {
        $stmt = $pdo->prepare("SELECT name, price, category FROM products ORDER BY is_popular DESC, name ASC LIMIT :lim");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function formatMenuList(array $items): string
{
    $lines = [];
    foreach ($items as $item) {
        $lines[] = "• {$item['name']} — Rs. " . number_format($item['price'], 2);
    }
    return implode("\n", $lines);
}

// ---------------------------------------------------------
// 1) DYNAMIC MENU QUERIES — check first, since these need
//    live data straight from the `products` table.
// ---------------------------------------------------------
$menuTriggers = ['menu', 'full menu', 'what do you have', 'show me the menu', 'items'];
foreach ($menuTriggers as $trigger) {
    if (str_contains($messageLower, $trigger)) {
        $items = fetchMenuItems($pdo, null, 6);
        echo json_encode([
            'reply' => "Here are some of our favorites right now:\n" . formatMenuList($items) . "\n\nWant to see the full menu? Just click the Menu link above!",
            'type'  => 'menu',
            'items' => $items,
        ]);
        exit;
    }
}

$categoryMap = [
    'hot coffee'  => 'hot',
    'cold coffee' => 'cold',
    'specialty'   => 'specialty',
    'pastries'    => 'pastry',
    'pastry'      => 'pastry',
];
foreach ($categoryMap as $phrase => $cat) {
    if (str_contains($messageLower, $phrase)) {
        $items = fetchMenuItems($pdo, $cat, 5);
        echo json_encode([
            'reply' => ucfirst($cat) . " picks from our menu:\n" . formatMenuList($items),
            'type'  => 'menu',
            'items' => $items,
        ]);
        exit;
    }
}

// ---------------------------------------------------------
// 2) KEYWORD-RULE RESPONSES from `chatbot_responses` table
//    Each row stores comma-separated keywords; we match if
//    ANY keyword appears inside the user's message.
// ---------------------------------------------------------
$stmt = $pdo->query("SELECT keywords, response FROM chatbot_responses");
$rules = $stmt->fetchAll();

foreach ($rules as $rule) {
    $keywords = array_map('trim', explode(',', strtolower($rule['keywords'])));
    foreach ($keywords as $kw) {
        if ($kw !== '' && str_contains($messageLower, $kw)) {
            echo json_encode([
                'reply' => $rule['response'],
                'type'  => 'text',
            ]);
            exit;
        }
    }
}

// ---------------------------------------------------------
// 3) GEMINI AI FALLBACK — Handle complex or off-topic queries
// ---------------------------------------------------------

// 🔑 PASTE YOUR REAL GEMINI API KEY HERE (Starts with AIzaSy...)
$geminiApiKey = "YOUR_GEMINI_API_KEY";


if (!empty($geminiApiKey) && $geminiApiKey !== "AQ"
) {
    
    // DB එකෙන් menu data එකතු කරගැනීම (AI context එක සඳහා)
    $menuContext = "";
    try {
        $allProducts = fetchMenuItems($pdo, null, 10);
        foreach ($allProducts as $p) {
            $menuContext .= "- {$p['name']} ({$p['category']}): Rs. {$p['price']}\n";
        }
    } catch (Exception $e) {
        $menuContext = "Menu items available on the website.";
    }

    $systemInstruction = "You are 'BrewBot', an exceptionally warm, friendly, enthusiastic, and polite AI Barista at 'Highland Roast Coffee Shop' ☕.

YOUR PERSONALITY & VOICE:
- Be super welcoming, polite, and caring—like a real barista who loves making people's day brighter!
- Use cheerful, relevant emojis (☕, ✨, 😊, 🥐, 🍰, 💛) naturally in your responses to keep the chat lively.
- Adapt to the customer's language seamlessly:
  * If they message in Sinhala, reply in warm, natural Sinhala.
  * If they message in Singlish (e.g., 'Meke price kiyada?'), reply in casual, friendly Singlish.
  * If they message in English, reply in friendly English.
- Keep replies concise, clear, and easy to read (1–3 sentences for general chat, bullet points for lists).

CORE CAPABILITIES & RULES:
1. RECOMMENDATIONS: Always suggest beverages/pastries based on how the customer feels or what they like. (e.g., If tired -> suggest a strong Espresso or Dark Roast; if sweet tooth -> Caramel Latte or Pastry).
2. MENU & PRICING: Mention prices in LKR (Rs.). Always rely on the dynamic menu data provided below.
3. CAFE INFO: 
   - Opening Hours: Monday to Sunday, 7:00 AM – 10:00 PM.
   - Location: 123 Coffee Avenue, Colombo 03.
   - Delivery: Available via UberEats, PickMe, or Direct Website Orders.
4. HELPFUL ASSISTANCE: Guide users on how to order from the website menu or check out their cart if they ask.

OUR LIVE MENU FROM DATABASE:
" . $menuContext;

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $geminiApiKey;

    $payload = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => $systemInstruction . "\n\nCustomer Message: " . $userMessage]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Local XAMPP SSL Issues මඟහැරීමට
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $resData = json_decode($response, true);
        $aiReply = $resData['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($aiReply) {
            echo json_encode([
                'reply' => $aiReply,
                'type'  => 'text',
            ]);
            exit;
        }
    }
}

// ---------------------------------------------------------
// 4) BASIC FALLBACK — If API Key is missing or API fails
// ---------------------------------------------------------
echo json_encode([
    'reply' => "I'm still learning! 🌱 Try asking about our menu, opening hours, delivery, or say something like \"I want a strong coffee\" and I'll recommend a drink.",
    'type'  => 'text',
]);