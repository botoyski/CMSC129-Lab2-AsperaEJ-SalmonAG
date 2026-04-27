# CMSC 129 Lab 3 - AI-Enhanced Task Management (Laravel MVC)

## Overview
This project extends the Lab 2 Laravel MVC CRUD app by integrating AI features:

- AI Chatbot (Inquiry Mode): answers natural-language questions about your tasks and categories.
- AI Assistant (CRUD Mode): performs task operations through natural language (create, update status/priority, archive, restore).
- On-page floating chat widget: usable directly from the dashboard while viewing your tasks.
- Destructive operation confirmation: update/delete actions require explicit confirmation before execution.
- Intelligent context awareness: supports follow-up filtering and pronoun-based references from recent results.

## AI Service and Model
This implementation uses an OpenAI-compatible Chat Completions API via backend proxy.

Default configuration:
- Provider endpoint: `https://api.openai.com/v1`
- Model: `gpt-4o-mini`

You can switch to other compatible providers (Groq, other OpenAI-compatible endpoints) by changing environment variables.

## Tech Stack
- Laravel 12
- PHP 8.3
- PostgreSQL
- Blade + Alpine.js
- Tailwind CSS (Vite)
- OpenAI-compatible LLM API (server-side only)

## Lab 3 Requirements Coverage

### 1. AI Chatbot for Inquiries
Implemented in Inquiry Mode:
- Accepts natural language input in the dashboard chat widget.
- Retrieves task/category data from database through backend logic.
- Returns conversational responses.
- Supports conversation context (recent messages stored in session, last 5-10 turns).
- Handles unclear prompts with graceful fallback examples.
- Supports follow-up filtering from previous results (e.g., high-priority from previous list, then due this week).
- Supports implicit references like "it/its" to the last focused task in context.

Supported inquiry types include:
1. Tasks due today
2. Tasks by priority (High/Medium/Low)
3. Count of completed tasks
4. Oldest pending task
5. Tasks by category
6. Total category count
7. General task listing

### 2. Dummy Data
- Seeder creates at least 20 sample tasks for the test user.
- Includes varied statuses, priorities, due dates, and categories.

### 3. On-Page Chat Interface
- Floating chat button on dashboard.
- Toggle open/close modal widget.
- Message history display.
- Input + send button.
- Loading state while AI is processing.
- Error messaging for failures.
- Inquiry/CRUD mode switch inside chat panel.
- Confirmation panel with Confirm/Cancel buttons for destructive actions.

### Expanded Requirements (Perfect Score Targets)
- CRUD via natural language commands
  - Create, Read, Update, Delete are supported through assistant mode.
  - Tool-style backend dispatch executes operations server-side.
  - Update/Delete require confirmation before changes are applied.
- Intelligent context awareness
  - Conversation history maintained in session (last 10+ messages).
  - Follow-up questions can filter prior results progressively.
  - Pronoun/implicit references use last focused task context.

## Security and Best Practices
- API keys are never exposed to frontend.
- All LLM calls are made from backend route: `POST /assistant/chat`.
- Uses environment variables for secrets.
- Route is authenticated and rate-limited (`throttle:20,1`).
- AI never receives direct database credentials or direct DB access.

## Project Structure (AI Additions)
- `app/Http/Controllers/AiAssistantController.php`
  - Validates requests
  - Manages inquiry and CRUD operations
  - Maintains chat context in session
- `app/Services/Ai/LlmChatService.php`
  - Calls OpenAI-compatible API
  - Performs intent parsing and response composition
- `routes/web.php`
  - Adds authenticated assistant endpoint
- `resources/views/layouts/app.blade.php`
  - Implements interactive chat widget and Alpine chat logic
- `config/services.php`
  - Adds `ai_assistant` config
- `.env.example`
  - Adds required LLM environment variables

## Environment Variables
Add to `.env`:

```dotenv
LLM_API_KEY=your_real_api_key_here
LLM_BASE_URL=https://api.openai.com/v1
LLM_MODEL=gpt-4o-mini
LLM_TIMEOUT_SECONDS=20
```

Notes:
- Do not commit real API keys.
- Keep `.env` local only.
- Commit `.env.example` only.

## Setup Instructions
1. Install dependencies:

```powershell
composer install
npm install
```

2. Create environment file:

```powershell
Copy-Item .env.example .env -Force
```

3. Configure database and AI environment values in `.env`.

4. Generate app key:

```powershell
php artisan key:generate
```

5. Run migrations and seeders:

```powershell
php artisan migrate --seed
```

6. Start app (2 terminals):

Terminal 1:
```powershell
npm run dev
```

Terminal 2:
```powershell
php artisan serve
```

7. Open app:
- `http://127.0.0.1:8000`

## Example Queries (Inquiry Mode)
- What tasks are due today?
- Show me all high-priority tasks.
- How many completed tasks do I have?
- What is my oldest pending task?
- List tasks in the Work category.
- How many categories are there?

## Example Commands (CRUD Mode)
- Create task "Prepare Lab 3 slides" due 2026-05-03.
- Mark "Prepare Lab 3 slides" as Completed.
- Set priority of "Prepare Lab 3 slides" to High.
- Change the due date of "Prepare Lab 3 slides" to next Friday.
- Archive task "Prepare Lab 3 slides".
- Restore task "Prepare Lab 3 slides".
- Delete all completed tasks from last month.

Notes for destructive operations:
- For Update/Delete requests, the assistant responds with a confirmation prompt first.
- You must click Confirm (or type Confirm) before the operation executes.

## Troubleshooting
1. Assistant unavailable / fallback replies only
- Check `LLM_API_KEY`, `LLM_BASE_URL`, and `LLM_MODEL` in `.env`.
- Verify outbound internet access.
- Clear config cache:

```powershell
php artisan config:clear
```

2. Database issues
- Verify PostgreSQL connection settings in `.env`.
- Ensure `pdo_pgsql` and `pgsql` extensions are enabled.

3. Frontend not updating
- Confirm Vite is running (`npm run dev`).
- Hard refresh browser after changes.

## Screenshots to Add for Submission
- Inquiry mode interaction (at least 2 sample questions)
- CRUD mode interaction (create/update/delete or restore)
- Dashboard with floating chat widget visible
- Error state sample (optional but recommended)
