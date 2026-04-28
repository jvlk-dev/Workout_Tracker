# Web Workout Tracker
A minimalist, workout tracking web application designed for focused training sessions. Unlike generic fitness apps, this project focuses on historical data visualization and session-specific feedback to help users achieve progressive overload.
# 🚀 The Vision
The project was born out of a need for a "data-first" workout logger—modeled after media tracking sites like Anilist. It provides a clean, distraction-free "Dark Mode" interface that functions as a digital training partner rather than just a static logbook.
<img width="3394" height="1290" alt="image" src="https://github.com/user-attachments/assets/6734014a-6c35-4f64-b189-00424d21b488" />

# 🛠️ Core Features
Dynamic Template Engine: Users can create custom workout templates (e.g., "Push Day," "Legs") with specific exercises and defined set counts.
<img width="934" height="1289" alt="image" src="https://github.com/user-attachments/assets/121bcce6-5965-45fe-8e2e-b7824e71cade" />
Intelligent Logging: The interface automatically pulls data from the last performance of a specific exercise, displaying it as a placeholder to encourage progressive overload.
<img width="883" height="1138" alt="image" src="https://github.com/user-attachments/assets/f83fa300-f2af-43e8-9103-b169dffffa23" />
Difficulty Feedback Loop: Each set is rated by the user (Easy, Moderate, Hard). The system uses this to apply visual color-coding to future sessions, signaling when to increase or decrease weight.
## Real-time Session Monitoring:
Live Session Timer: Tracks total workout duration.
Manual Rest Timer: A floating widget with quick-adjust controls (+/- 15s) to manage recovery periods without leaving the logging screen.
Comprehensive History: A deep-scroll history within each template allows users to review past performance, notes, and duration at a glance.
Multi-User Security: Full authentication system (Login/Signup) with password hashing and session-based data isolation.
# 💻 Tech Stack
Frontend: HTML5, CSS3 (Custom Variables/Dark Mode), JavaScript (Vanilla ES6).
Backend: PHP 8.x.
Database: MariaDB (Relational structure).
Authentication: PHP Sessions & password_hash encryption.
