/**
 * Exam JavaScript - Core exam taking functionality
 */

class ExamManager {
    constructor() {
        this.attemptId = null;
        this.exam = null;
        this.questions = [];
        this.currentQuestionIndex = 0;
        this.answers = {};
        this.skipped = new Set();
        this.startTime = Date.now();
        this.timerInterval = null;
        this.durationSeconds = 0;
        this.tabSwitchCount = 0;
        this.focusLostCount = 0;

        this.init();
    }

    async init() {
        this.setupEventListeners();
        this.setupSecurityFeatures();
        await this.startExam();
    }

    setupEventListeners() {
        document.getElementById('prevBtn').addEventListener('click', () => this.previousQuestion());
        document.getElementById('nextBtn').addEventListener('click', () => this.nextQuestion());
        document.getElementById('skipBtn').addEventListener('click', () => this.skipQuestion());
        document.getElementById('submitBtn').addEventListener('click', () => this.showSubmitModal());
        document.getElementById('cancelSubmitBtn').addEventListener('click', () => this.hideSubmitModal());
        document.getElementById('confirmSubmitBtn').addEventListener('click', () => this.submitExam());
        
        document.getElementById('calculatorBtn').addEventListener('click', () => this.showCalculator());
        document.getElementById('closeCalculatorBtn').addEventListener('click', () => this.hideCalculator());

        // Prevent right-click
        document.addEventListener('contextmenu', e => e.preventDefault());
        
        // Prevent back navigation
        history.pushState(null, null, location.href);
        window.addEventListener('popstate', e => {
            history.pushState(null, null, location.href);
        });
    }

    setupSecurityFeatures() {
        // Track tab switches
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.tabSwitchCount++;
                this.trackAction('tab_switch');
            }
        });

        // Track focus loss
        window.addEventListener('blur', () => {
            this.focusLostCount++;
            this.trackAction('focus_lost');
        });

        // Disable F12, Ctrl+Shift+I, Ctrl+Shift+C
        document.addEventListener('keydown', e => {
            if (e.key === 'F12' || 
                (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'C'))) {
                e.preventDefault();
                return false;
            }
        });
    }

    async startExam() {
        const examCode = document.getElementById('examCode').value;
        const csrfToken = document.getElementById('csrfToken').value;

        try {
            const response = await fetch('/exam/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': csrfToken,
                },
                body: `exam_code=${encodeURIComponent(examCode)}`
            });

            const data = await response.json();

            if (!data.success) {
                alert('Error: ' + (data.error || 'Failed to start exam'));
                window.location.href = '/dashboard/student';
                return;
            }

            this.attemptId = data.attempt_id;
            this.exam = data.exam;
            this.questions = data.questions;
            this.durationSeconds = data.duration_seconds;

            document.getElementById('examTitle').textContent = this.exam.title;
            document.getElementById('examSubject').textContent = this.exam.subject_name;
            document.getElementById('totalCount').textContent = this.questions.length;

            this.renderQuestion(0);
            this.startTimer();
        } catch (error) {
            console.error('Exam start error:', error);
            alert('Failed to load exam');
        }
    }

    renderQuestion(index) {
        if (index < 0 || index >= this.questions.length) return;

        this.currentQuestionIndex = index;
        const question = this.questions[index];

        // Update palette
        this.updatePalette();

        // Render question
        const container = document.getElementById('questionContainer');
        container.classList.remove('loading');
        container.innerHTML = `
            <h2 class="question-text">
                Question ${index + 1} of ${this.questions.length}
            </h2>
            <p class="question-text">${escapeHtml(question.question_text)}</p>
        `;

        // Render options
        const optionsContainer = document.getElementById('optionsContainer');
        optionsContainer.innerHTML = '';

        const options = Array.isArray(question.options) ? question.options : [];
        
        // Shuffle options if needed
        if (this.exam.randomize_options) {
            options.sort(() => Math.random() - 0.5);
        }

        options.forEach((option, idx) => {
            const btn = document.createElement('button');
            btn.className = 'option-btn';
            btn.innerHTML = `
                <span class="option-label">${option.option_letter}</span>
                <span class="option-text">${escapeHtml(option.option_text)}</span>
            `;

            // Check if selected
            if (this.answers[question.id] === option.id) {
                btn.classList.add('selected');
            }

            btn.addEventListener('click', () => this.selectOption(question.id, option.id, btn));
            optionsContainer.appendChild(btn);
        });

        // Show question image if exists
        if (question.question_image) {
            const imgContainer = document.getElementById('questionImageContainer');
            imgContainer.innerHTML = `<img src="/storage/uploads/questions/${question.question_image}" alt="Question">`;
            imgContainer.classList.remove('hidden');
        } else {
            document.getElementById('questionImageContainer').classList.add('hidden');
        }

        // Update navigation buttons
        document.getElementById('prevBtn').disabled = index === 0;
        document.getElementById('nextBtn').disabled = index === this.questions.length - 1;
    }

    updatePalette() {
        const paletteList = document.getElementById('paletteList');
        paletteList.innerHTML = '';

        let answeredCount = 0;
        let skippedCount = 0;

        this.questions.forEach((q, idx) => {
            const btn = document.createElement('button');
            btn.className = 'question-btn';
            btn.textContent = idx + 1;

            if (idx === this.currentQuestionIndex) {
                btn.classList.add('current');
            } else if (this.answers[q.id] !== undefined) {
                btn.classList.add('answered');
                answeredCount++;
            } else if (this.skipped.has(q.id)) {
                btn.classList.add('skipped');
                skippedCount++;
            }

            btn.addEventListener('click', () => this.renderQuestion(idx));
            paletteList.appendChild(btn);
        });

        document.getElementById('answeredCount').textContent = answeredCount;
        document.getElementById('skippedCount').textContent = skippedCount;
        document.getElementById('confirmAnswered').textContent = answeredCount;
        document.getElementById('confirmSkipped').textContent = skippedCount;
    }

    selectOption(questionId, optionId, btn) {
        this.answers[questionId] = optionId;
        this.skipped.delete(questionId);

        // Update UI
        document.querySelectorAll('.option-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');

        // Auto-save
        this.saveAnswer(questionId, optionId, false);

        // Update palette
        this.updatePalette();
    }

    async saveAnswer(questionId, optionId, isSkipped = false) {
        try {
            await fetch('/exam/save-answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.getElementById('csrfToken').value,
                },
                body: `attempt_id=${this.attemptId}&question_id=${questionId}&option_id=${optionId}&is_skipped=${isSkipped}`
            });
        } catch (error) {
            console.error('Save answer error:', error);
        }
    }

    skipQuestion() {
        const question = this.questions[this.currentQuestionIndex];
        this.skipped.add(question.id);
        delete this.answers[question.id];
        this.saveAnswer(question.id, null, true);
        this.nextQuestion();
    }

    nextQuestion() {
        if (this.currentQuestionIndex < this.questions.length - 1) {
            this.renderQuestion(this.currentQuestionIndex + 1);
            window.scrollTo(0, 0);
        }
    }

    previousQuestion() {
        if (this.currentQuestionIndex > 0) {
            this.renderQuestion(this.currentQuestionIndex - 1);
            window.scrollTo(0, 0);
        }
    }

    startTimer() {
        let remaining = this.durationSeconds;

        this.timerInterval = setInterval(() => {
            remaining--;

            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            const timeText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            document.getElementById('timerText').textContent = timeText;

            // Update progress bar
            const progress = (remaining / this.durationSeconds) * 100;
            document.getElementById('timerProgress').style.width = progress + '%';

            // Critical warning when < 5 minutes
            const display = document.querySelector('.timer-display');
            if (remaining < 300) {
                display.classList.add('critical');
            } else {
                display.classList.remove('critical');
            }

            // Auto-submit when timer ends
            if (remaining <= 0) {
                clearInterval(this.timerInterval);
                this.autoSubmit('Timer ended');
            }
        }, 1000);
    }

    async trackAction(action) {
        try {
            await fetch('/exam/track-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.getElementById('csrfToken').value,
                },
                body: `attempt_id=${this.attemptId}&action=${action}`
            });
        } catch (error) {
            console.error('Track action error:', error);
        }
    }

    showSubmitModal() {
        document.getElementById('submitModal').classList.remove('hidden');
    }

    hideSubmitModal() {
        document.getElementById('submitModal').classList.add('hidden');
    }

    async submitExam() {
        clearInterval(this.timerInterval);
        const csrfToken = document.getElementById('csrfToken').value;

        try {
            const response = await fetch('/exam/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': csrfToken,
                },
                body: `attempt_id=${this.attemptId}`
            });

            const data = await response.json();

            if (data.success) {
                alert('Exam submitted successfully!');
                window.location.href = '/dashboard/student';
            } else {
                alert('Error: ' + (data.error || 'Failed to submit exam'));
            }
        } catch (error) {
            console.error('Submit exam error:', error);
            alert('Failed to submit exam');
        }
    }

    async autoSubmit(reason) {
        try {
            const response = await fetch('/exam/auto-submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': document.getElementById('csrfToken').value,
                },
                body: `attempt_id=${this.attemptId}&reason=${encodeURIComponent(reason)}`
            });

            const data = await response.json();
            if (data.success) {
                alert('Exam automatically submitted due to: ' + reason);
                window.location.href = '/dashboard/student';
            }
        } catch (error) {
            console.error('Auto-submit error:', error);
        }
    }

    showCalculator() {
        document.getElementById('calculatorModal').classList.remove('hidden');
    }

    hideCalculator() {
        document.getElementById('calculatorModal').classList.add('hidden');
    }
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ExamManager();
});
