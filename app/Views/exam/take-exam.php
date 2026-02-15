<?php
/**
 * Exam View - Student takes exam
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam - BrainToper</title>
    <link rel="stylesheet" href="/assets/css/exam.css">
</head>
<body class="exam-page">
    <div class="exam-wrapper">
        <!-- Header -->
        <header class="exam-header">
            <div class="exam-info">
                <h1 id="examTitle"></h1>
                <p id="examSubject"></p>
            </div>
            <div class="exam-controls">
                <button class="btn-calculator" id="calculatorBtn" title="Scientific Calculator">
                    <span class="icon">∑</span>
                </button>
                <button class="btn-logout" id="logoutBtn" title="Logout">
                    <span class="icon">⏚</span>
                </button>
            </div>
        </header>

        <div class="exam-container">
            <!-- Left: Question Palette -->
            <aside class="question-palette">
                <div class="palette-header">
                    <h3>Questions</h3>
                    <div class="palette-stats">
                        <span class="stat">
                            <strong id="answeredCount">0</strong>
                            <small>Answered</small>
                        </span>
                        <span class="stat">
                            <strong id="skippedCount">0</strong>
                            <small>Skipped</small>
                        </span>
                        <span class="stat">
                            <strong id="totalCount">0</strong>
                            <small>Total</small>
                        </span>
                    </div>
                </div>
                <div class="palette-list" id="paletteList"></div>
            </aside>

            <!-- Center: Exam Content -->
            <main class="exam-content">
                <!-- Timer -->
                <div class="timer-bar">
                    <div class="timer-display">
                        <span id="timerText" class="timer-text">60:00</span>
                        <div id="timerProgress" class="timer-progress" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Question -->
                <div class="question-section">
                    <div id="questionContainer" class="question-container loading">
                        <div class="skeleton"></div>
                    </div>

                    <!-- Answer Options -->
                    <div id="optionsContainer" class="options-container">
                        <!-- Options will be inserted here -->
                    </div>

                    <!-- Question Image -->
                    <div id="questionImageContainer" class="question-image-container hidden"></div>
                </div>

                <!-- Navigation -->
                <div class="exam-navigation">
                    <button class="btn btn-secondary" id="prevBtn" disabled>
                        <span class="icon">←</span> Previous
                    </button>
                    <button class="btn btn-secondary" id="skipBtn">
                        <span class="icon">⊗</span> Skip
                    </button>
                    <button class="btn btn-primary" id="nextBtn">
                        Next <span class="icon">→</span>
                    </button>
                    <button class="btn btn-success" id="submitBtn">
                        <span class="icon">✓</span> Submit Exam
                    </button>
                </div>
            </main>
        </div>
    </div>

    <!-- Scientific Calculator Modal -->
    <div id="calculatorModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Scientific Calculator</h3>
                <button class="btn-close" id="closeCalculatorBtn">&times;</button>
            </div>
            <div class="calculator">
                <input type="text" id="calcDisplay" class="calc-display" readonly value="0">
                <div class="calc-buttons">
                    <button class="calc-btn" data-value="C">C</button>
                    <button class="calc-btn" data-value="DEL">DEL</button>
                    <button class="calc-btn" data-value="/">/</button>
                    <button class="calc-btn" data-value="*">×</button>

                    <button class="calc-btn" data-value="7">7</button>
                    <button class="calc-btn" data-value="8">8</button>
                    <button class="calc-btn" data-value="9">9</button>
                    <button class="calc-btn" data-value="-">−</button>

                    <button class="calc-btn" data-value="4">4</button>
                    <button class="calc-btn" data-value="5">5</button>
                    <button class="calc-btn" data-value="6">6</button>
                    <button class="calc-btn" data-value="+">+</button>

                    <button class="calc-btn" data-value="1">1</button>
                    <button class="calc-btn" data-value="2">2</button>
                    <button class="calc-btn" data-value="3">3</button>
                    <button class="calc-btn" data-value="=">=</button>

                    <button class="calc-btn" data-value="0">0</button>
                    <button class="calc-btn" data-value=".">.</button>
                    <button class="calc-btn" data-value="sqrt">√</button>
                    <button class="calc-btn" data-value="pow">x²</button>

                    <button class="calc-btn" data-value="sin">sin</button>
                    <button class="calc-btn" data-value="cos">cos</button>
                    <button class="calc-btn" data-value="tan">tan</button>
                    <button class="calc-btn" data-value="log">log</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Confirmation Modal -->
    <div id="submitModal" class="modal hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Submit Exam?</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit your exam?</p>
                <p>You <strong>cannot</strong> change your answers after submission.</p>
                <div class="submission-stats">
                    <div class="stat-box">
                        <strong id="confirmAnswered">0</strong>
                        <small>Answered</small>
                    </div>
                    <div class="stat-box">
                        <strong id="confirmSkipped">0</strong>
                        <small>Skipped</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelSubmitBtn">Cancel</button>
                <button class="btn btn-success" id="confirmSubmitBtn">Yes, Submit</button>
            </div>
        </div>
    </div>

    <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" id="examCode" value="<?= htmlspecialchars($examCode) ?>">

    <script src="/assets/js/exam.js"></script>
    <script src="/assets/js/calculator.js"></script>
    <link rel="stylesheet" href="/assets/css/calculator.css">
</body>
</html>
