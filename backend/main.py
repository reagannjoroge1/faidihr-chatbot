from fastapi import FastAPI, Query
from fastapi.middleware.cors import CORSMiddleware
import json
import os
import re

app = FastAPI()

# Allow Yii2 frontend to access FastAPI backend
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

sessions = {}
session_states = {}

# Define greetings in multiple languages
GREETINGS = {
    "en": ["hello", "hi", "hey", "good morning", "good afternoon", "good evening"],
    "sw": ["habari", "niaje", "jambo", "salamu", "mambo", "niaje", "vipi"],
    "fr": ["bonjour", "salut", "coucou"],
    "es": ["hola", "buenos días", "buenas tardes"],
}

# Greeting responses
GREET_RESPONSES = {
    "en": "Hello! How can I help you today?",
    "sw": "Habari! Naweza kukusaidiaje leo?",
    "fr": "Bonjour! Comment puis-je vous aider aujourd'hui?",
    "es": "¡Hola! ¿Cómo puedo ayudarte hoy?",
}


# Load manual.json safely
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MANUAL_PATH = os.path.join(BASE_DIR, "manual.json")

try:
    with open(MANUAL_PATH, "r") as f:
        manual_data = json.load(f)
except FileNotFoundError:
    manual_data = []
    print(f"⚠️ Manual file not found at {MANUAL_PATH}")

@app.get("/")
async def root():
    return {"message": "FastAPI backend is running!"}

@app.get("/chat")
async def chat(q: str = Query(...), session_id: str = "default"):
    query = q.lower().strip()
    sessions.setdefault(session_id, [])
    session_states.setdefault(session_id, None)

    # Track last topic to detect unrelated queries
    last_topic = sessions[session_id][-1]["topic"] if sessions[session_id] else None

    # Record current user query
    sessions[session_id].append({"user": query, "topic": last_topic})

    # --- 1️⃣ Detect greetings ---
    for lang, greetings in GREETINGS.items():
        if any(greet in query for greet in greetings):
            response = GREET_RESPONSES[lang]
            session_states[session_id] = None  # reset confirmation
            return {
                "topic": "GREETING",
                "content": response,
                "url": ""
            }

    # --- 2️⃣ Smart keyword/semantic matching ---
    def normalize(text):
        return re.sub(r"[^a-zA-Z0-9\s]", "", text.lower()).strip()

    normalized_query = normalize(query)
    words = normalized_query.split()

    best_match = None
    max_overlap = 0

    for entry in manual_data:
        topic = (entry.get("topic") or entry.get("Topic") or "").lower()
        content = entry.get("content") or entry.get("Content") or ""
        url = entry.get("url") or entry.get("URL", "")

        if not topic or not content:
            continue

        topic_words = topic.split()
        overlap = len(set(words) & set(topic_words))

        if overlap > max_overlap or topic in normalized_query:
            max_overlap = overlap
            best_match = (topic.upper(), content, url)

    # --- 3️⃣ Handle match ---
    if best_match:
        topic, content, url = best_match

        # Check if new query is unrelated to previous topic
        if last_topic and last_topic != topic:
            session_states[session_id] = None

        session_states[session_id] = "awaiting_confirmation"
        return {
            "topic": topic,
            "content": content,
            "url": url
        }

    # --- 4️⃣ Handle confirmation state ---
    if session_states[session_id] == "awaiting_confirmation":
        if "yes" in query:
            session_states[session_id] = "awaiting_input"
            return {
                "topic": "ASSIST MODE",
                "content": "🧩 Great! Please provide the details or upload the file required for this task.",
                "url": ""
            }
        elif "no" in query:
            session_states[session_id] = None
            sessions[session_id] = []
            return {
                "topic": "OKAY",
                "content": "Alright! 💬 What else would you like me to help you with?",
                "url": ""
            }
        else:
            # Only ask for confirmation if the query seems related
            return {
                "topic": "CONFIRMATION",
                "content": "Please reply with 'yes' or 'no' so I know whether to assist further.",
                "url": ""
            }

    # --- 5️⃣ No match found fallback ---
    session_states[session_id] = None
    return {
        "topic": "NOT FOUND",
        "content": "Sorry, I couldn't find information related to your request. Try rephrasing or selecting a quick action.",
        "url": ""
    }


@app.get("/quick_actions")
def get_quick_actions():
    return {
        "actions": [
            "APPLY LEAVE",
            "IMPORT EMPLOYEES",
            "CHECK MY PROFILE",
            "CHECK YOUR BENEFITS",
            "MANAGE SHIFTS",
            "SUPERVISOR APPROVAL"
        ]
    }

@app.get("/new_chat")
def new_chat(session_id: str = "default"):
    sessions[session_id] = []
    session_states[session_id] = None
    return {"message": "Chat context cleared successfully!"}
