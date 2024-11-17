<?php
// api/chat.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

class ChatHandler {
    private $pythonScript;
    private $logFile;
    
    public function __construct() {
        $this->pythonScript = __DIR__ . '/ryan_ai.py';
        $this->logFile = __DIR__ . '/debug.log';
        $this->logMessage("ChatHandler initialized");
    }
    
    private function logMessage($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    public function handleRequest() {
        try {
            // Log the incoming request
            $rawInput = file_get_contents('php://input');
            $this->logMessage("Received input: " . $rawInput);

            // Validate input
            $input = json_decode($rawInput);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }

            if (!$input || !isset($input->message)) {
                throw new Exception('Invalid input format');
            }

            $message = trim($input->message);
            if (empty($message)) {
                throw new Exception('Empty message');
            }

            // Check if Python script exists
            if (!file_exists($this->pythonScript)) {
                throw new Exception('Python script not found at: ' . $this->pythonScript);
            }

            // Get response from Python script
            $response = $this->getResponse($message);
            
            if ($response === false) {
                throw new Exception('Failed to get response from AI');
            }

            $this->logMessage("Successfully processed message. Response: " . json_encode($response));
            echo json_encode(['response' => $response]);
            
        } catch (Exception $e) {
            $this->logMessage("Error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'error' => $e->getMessage(),
                'details' => 'Check server logs for more information'
            ]);
        }
    }

    private function getResponse($message) {
        $sanitized_message = escapeshellarg($message);
        $command = "python3 " . escapeshellarg($this->pythonScript) . " " . $sanitized_message;
        
        $this->logMessage("Executing command: " . $command);
        
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            
            foreach ($pipes as $pipe) {
                fclose($pipe);
            }
            
            $return_value = proc_close($process);

            $this->logMessage("Python script output: " . $output);
            if (!empty($error)) {
                $this->logMessage("Python script error: " . $error);
            }

            if ($return_value !== 0) {
                $this->logMessage("Process returned non-zero exit code: " . $return_value);
                return false;
            }

            $decoded = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logMessage("JSON decode error: " . json_last_error_msg());
                return false;
            }

            return $decoded['response'] ?? false;
        }
        
        $this->logMessage("Failed to create process");
        return false;
    }
}

$handler = new ChatHandler();
$handler->handleRequest();
?>