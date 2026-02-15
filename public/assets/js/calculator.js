/**
 * Scientific Calculator
 */

class ScientificCalculator {
    constructor() {
        this.display = document.getElementById('calcDisplay');
        this.currentInput = '0';
        this.previousInput = '';
        this.operation = null;
        this.shouldResetDisplay = false;

        this.init();
    }

    init() {
        const buttons = document.querySelectorAll('.calc-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', () => this.handleButtonClick(btn.dataset.value));
        });
    }

    handleButtonClick(value) {
        switch (value) {
            case 'C':
                this.clear();
                break;
            case 'DEL':
                this.delete();
                break;
            case '=':
                this.calculate();
                break;
            case '+':
            case '-':
            case '*':
            case '/':
                this.setOperation(value);
                break;
            case 'sqrt':
                this.sqrt();
                break;
            case 'pow':
                this.power();
                break;
            case 'sin':
                this.trig('sin');
                break;
            case 'cos':
                this.trig('cos');
                break;
            case 'tan':
                this.trig('tan');
                break;
            case 'log':
                this.log();
                break;
            default:
                this.appendNumber(value);
        }
    }

    appendNumber(num) {
        if (this.shouldResetDisplay) {
            this.currentInput = num;
            this.shouldResetDisplay = false;
        } else {
            if (this.currentInput === '0' && num !== '.') {
                this.currentInput = num;
            } else if (num !== '.' || !this.currentInput.includes('.')) {
                this.currentInput += num;
            }
        }
        this.updateDisplay();
    }

    setOperation(op) {
        if (this.operation !== null) {
            this.calculate();
        }
        this.previousInput = this.currentInput;
        this.operation = op;
        this.shouldResetDisplay = true;
    }

    calculate() {
        if (this.operation === null || this.previousInput === '') return;

        const prev = parseFloat(this.previousInput);
        const current = parseFloat(this.currentInput);

        let result;
        switch (this.operation) {
            case '+':
                result = prev + current;
                break;
            case '-':
                result = prev - current;
                break;
            case '*':
                result = prev * current;
                break;
            case '/':
                result = prev / current;
                break;
            default:
                return;
        }

        this.currentInput = String(result);
        this.operation = null;
        this.shouldResetDisplay = true;
        this.updateDisplay();
    }

    sqrt() {
        const value = parseFloat(this.currentInput);
        this.currentInput = String(Math.sqrt(value));
        this.shouldResetDisplay = true;
        this.updateDisplay();
    }

    power() {
        const value = parseFloat(this.currentInput);
        this.currentInput = String(value * value);
        this.shouldResetDisplay = true;
        this.updateDisplay();
    }

    trig(func) {
        const value = parseFloat(this.currentInput);
        const radians = value * (Math.PI / 180);

        let result;
        switch (func) {
            case 'sin':
                result = Math.sin(radians);
                break;
            case 'cos':
                result = Math.cos(radians);
                break;
            case 'tan':
                result = Math.tan(radians);
                break;
        }

        this.currentInput = String(result.toFixed(8));
        this.shouldResetDisplay = true;
        this.updateDisplay();
    }

    log() {
        const value = parseFloat(this.currentInput);
        this.currentInput = String(Math.log10(value));
        this.shouldResetDisplay = true;
        this.updateDisplay();
    }

    delete() {
        if (this.currentInput.length > 1) {
            this.currentInput = this.currentInput.slice(0, -1);
        } else {
            this.currentInput = '0';
        }
        this.updateDisplay();
    }

    clear() {
        this.currentInput = '0';
        this.previousInput = '';
        this.operation = null;
        this.shouldResetDisplay = false;
        this.updateDisplay();
    }

    updateDisplay() {
        this.display.value = this.currentInput;
    }
}

// Initialize calculator
document.addEventListener('DOMContentLoaded', () => {
    new ScientificCalculator();
});
