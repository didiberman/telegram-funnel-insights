# 3-Day Anxiety Reset Course

A free, interactive web course to help users heal anxiety, trauma, and emotional overwhelm through body-based practices and daily journaling. This project combines a modern, visually engaging frontend with a privacy-focused PHP backend for secure journaling, progress tracking, and user engagement.

---

## Features

- **Guided Video Modules:** Each day presents a new video lesson with actionable steps and journaling prompts.
- **Secure Journaling:** Users can reflect on daily prompts, save their responses, and export their journal as a PDF.
- **Progress Tracking:** Unique tracker IDs and cookies allow users to resume their journey, with backend logic to track completion and send reminders.
- **Automated Reminders:** Email and Telegram notifications help users stay on track.
- **Modern, Responsive UI:** Glassmorphism, animated gradients, and mobile-friendly layouts for an engaging experience.
- **Privacy-Focused:** Journal entries are securely stored and never shared; users can request deletion at any time.

---

## Project Structure

```
.
├── index.html / workingindex.html / integrated_index.html  # Main landing and course pages
├── day1.html / day2.html / day3.html                      # Daily video + journaling modules
├── journey_tracker.js                                     # Handles user tracking, cookies, and form population
├── submit_journal.php                                     # Receives and stores journal entries
├── update_journey.php                                     # Updates user progress
├── sub.php / unsub.php / tg.php / pv.php                  # Subscription, unsubscription, Telegram, and pageview logic
├── setup_database.php                                     # SQLite database setup
├── j.style.css / workingj.style.css / css/                # Main and alternate stylesheets
├── popup.html / popup.js                                  # Modal and popup logic
├── privacy-policy.html                                    # Privacy policy
├── .htaccess                                              # Redirects and security rules
├── journey_data.sqlite                                    # SQLite database (if present)
├── ...                                                    # Logs, images, and other assets
```

---

## How It Works

### Frontend

- **Landing Page:** Introduces the course, its benefits, and how it works.
- **Daily Modules:** Each day (e.g., `day1.html`) features a video lesson and a journaling form. Users must complete the journal to proceed.
- **Journaling:** Users’ answers are saved, exported as PDF, and submitted to the backend for progress tracking.
- **Visuals:** Uses modern CSS (glassmorphism, gradients, animations) for a calming, engaging experience.

### Tracking & Progress

- **Cookies:** Each user is assigned a unique tracker ID via cookies for anonymous progress tracking.
- **Hidden Fields:** Forms auto-populate hidden fields (user ID, email, etc.) for backend processing.
- **Reminders:** The backend can send reminders via email (MailerLite) and internal Telegram notifications.

### Backend

- **PHP Endpoints:** Handle journal submissions, progress updates, subscriptions, and unsubscriptions.
- **Database:** Uses SQLite for storing user data, journal entries, and progress.
- **Bot Protection:** Honeypot fields and cookie checks prevent spam and abuse.
- **Logging:** All key actions are logged for monitoring and debugging.
- **Security:** Data is stored on secure Google servers, with HTTPS enforced and access restricted.

---

## Privacy & Data Security

- **No Data Sharing:** Journal entries and user data are never shared or sold.
- **User Control:** Users can request deletion of their data at any time.
- **Encryption & Access:** Data is encrypted in transit (HTTPS) and access is limited to authorized personnel.
- **Compliance:** Designed with GDPR and privacy best practices in mind.

---

## Setup & Deployment

1. **Requirements:**  
   - PHP 7.4+  
   - SQLite (or compatible DB)  
   - Web server (Apache recommended for .htaccess rules)

2. **Installation:**  
   - Clone the repo to your web server.
   - Run `setup_database.php` to initialize the SQLite database.
   - Configure email and Telegram integration in the relevant PHP files (`sub.php`, `tg.php`).
   - Update `.htaccess` for your domain and security needs.

3. **Customization:**  
   - Edit video links, journaling prompts, and branding in the HTML files.
   - Adjust styles in `j.style.css` or `workingj.style.css`.

4. **Security:**  
   - Ensure your server uses HTTPS.
   - Restrict access to logs and sensitive files as per `.htaccess`.

---

## Integrations

- **MailerLite:** For email delivery and reminders.
- **Telegram Bot:** For internal notifications about user progress.
- **Wistia/YouTube:** For video hosting and embedding.

---

## License

This project is for educational and personal growth purposes.  
Contact the author for commercial use or collaboration.

---

## Author

[Didi Being](https://didibeing.social/)  
Contact: hey at didibeing dot social

---

## Contributing

Pull requests and suggestions are welcome! Please open an issue to discuss your ideas.

---

Let me know if you want to add installation commands, screenshots, or more technical details!
