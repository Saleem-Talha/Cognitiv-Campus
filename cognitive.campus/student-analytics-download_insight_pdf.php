<?php
// This file will generate a PDF from the insight data and provide it for download

// Include the necessary libraries (you'll need to install dompdf)
require_once 'vendor/autoload.php'; // Adjust path as needed to your vendor directory
use Dompdf\Dompdf;
use Dompdf\Options;

include("includes/db-connect.php");
include("includes/validation.php");

// Get insight ID from request
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No insight ID provided");
}

$insightId = $_GET['id'];
$userInfo = getUserInfo();
$userEmail = $userInfo['email'];

// Fetch the insight data
$stmt = $db->prepare("SELECT insight_text, created_at FROM student_grade_insights WHERE id = ? AND user_id = ?");
$stmt->bind_param("is", $insightId, $userEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Insight not found or you don't have permission to access it");
}

$insight = $result->fetch_assoc();

// Format the insight text for PDF (convert markdown-like syntax to HTML)
$insightText = $insight['insight_text'];

// Make headings bold
$insightText = preg_replace('/(#+)\s+(.*?)(\n|$)/m', '<strong>$2</strong><br>', $insightText);

// Replace bold text
$insightText = preg_replace('/\*\*(.*?)\*\*/m', '<strong>$1</strong>', $insightText);

// Process bullet points
$insightText = preg_replace('/- (.*?)(\n|$)/m', '<li>$1</li>', $insightText);

// Wrap bullet points in ul tags
$insightText = preg_replace('/(<li>.*?<\/li>)+/s', '<ul>$0</ul>', $insightText);

// Process numbered lists
$insightText = preg_replace('/(\d+)\.\s+(.*?)(\n|$)/m', '<li>$2</li>', $insightText);

// Wrap numbered lists in ol tags
$insightText = preg_replace('/(<li>.*?<\/li>)+/s', '<ol>$0</ol>', $insightText);

// Convert newlines to breaks for remaining text
$insightText = str_replace("\n", "<br>", $insightText);

// Create PDF content
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Analytics Insight</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .date {
            text-align: right;
            color: #666;
            font-style: italic;
            margin-bottom: 20px;
        }
        strong {
            color: #333;
            font-weight: bold;
            font-size: 1.1em;
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        ul, ol {
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.8em;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Analytics Insight</h1>
    </div>
    
    <div class="date">
        Generated on: ' . date('F j, Y, g:i a', strtotime($insight['created_at'])) . '
    </div>
    
    <div class="content">
        ' . $insightText . '
    </div>
    
    <div class="footer">
        This is an automatically generated report from your academic performance data.
    </div>
</body>
</html>
';

// Configure Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Arial');

// Create Dompdf instance
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render PDF (generate)
$dompdf->render();

// Set the PDF filename
$filename = 'student_insight_' . date('Y-m-d') . '.pdf';

// Stream the PDF to the browser for download
$dompdf->stream($filename, array('Attachment' => true));
exit();
?>