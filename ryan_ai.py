#!/usr/bin/env python3
import sys
import json
import random
import logging
from typing import Dict, List, Optional
from pathlib import Path

# Set up logging
logging.basicConfig(
    filename=Path(__file__).parent / 'ai_debug.log',
    level=logging.DEBUG,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

class RyanAI:
    def __init__(self):
        self.responses = {
            "greeting": [
                "Hello! How can I help you today?",
                "Hi there! How can I assist you?",
                "Hey! What can I do for you?",
                "Greetings! How may I help you?"
            ],
            "name": [
                "I am RyanAI, your virtual assistant.",
                "You can call me RyanAI!",
                "My name is RyanAI, nice to meet you!"
            ],
            "how_are_you": [
                "I'm functioning well and ready to help!",
                "I'm doing great, thank you for asking! How can I assist you?",
                "All systems operational and ready to help!"
            ],
            "default": [
                "I'm not sure I understand. Could you please rephrase that?",
                "I'm still learning. Could you try asking in a different way?",
                "I'm not quite sure what you mean. Can you elaborate?"
            ]
        }
        logging.info("RyanAI initialized")

    def get_response(self, user_input: str) -> str:
        """Generate a response based on user input."""
        logging.debug(f"Processing input: {user_input}")
        
        # Convert input to lowercase for matching
        user_input = user_input.lower().strip()
        
        # Check for greetings
        if any(word in user_input for word in ["hi", "hello", "hey", "greetings"]):
            response = random.choice(self.responses["greeting"])
        # Check for name questions
        elif "name" in user_input and ("your" in user_input or "who" in user_input):
            response = random.choice(self.responses["name"])
        # Check for how are you
        elif "how are you" in user_input:
            response = random.choice(self.responses["how_are_you"])
        # Default response
        else:
            response = random.choice(self.responses["default"])
            
        logging.debug(f"Generated response: {response}")
        return response

    def process_input(self, user_input: str) -> Dict[str, str]:
        """Process user input and return a response."""
        try:
            response = self.get_response(user_input)
            return {"response": response}
        except Exception as e:
            logging.error(f"Error processing input: {str(e)}", exc_info=True)
            return {"error": str(e)}

def main():
    """Main entry point for the script."""
    try:
        logging.info("Script started")
        
        if len(sys.argv) < 2:
            logging.error("No input provided")
            print(json.dumps({"error": "No input provided"}))
            return

        user_input = " ".join(sys.argv[1:])
        logging.info(f"Received input: {user_input}")

        ai = RyanAI()
        result = ai.process_input(user_input)
        
        logging.info(f"Sending response: {result}")
        print(json.dumps(result))
        
    except Exception as e:
        logging.error(f"Unexpected error: {str(e)}", exc_info=True)
        print(json.dumps({"error": str(e)}))

if __name__ == "__main__":
    main()