// ========================= HERO SLIDER =========================

// Select all slides
const slides = document.querySelectorAll(".slide");

let currentIndex = 0;

// Show next slide
function showNextSlide() {

    // Stop if no slides found
    if (slides.length === 0) {
        return;
    }

    // Remove active class from current slide
    slides[currentIndex].classList.remove("active");

    // Move to next slide
    currentIndex = (currentIndex + 1) % slides.length;

    // Add active class to next slide
    slides[currentIndex].classList.add("active");
}

// Start slider only if slides exist
if (slides.length > 0) {

    // First slide active
    slides[0].classList.add("active");

    // Auto slide every 3 seconds
    setInterval(showNextSlide, 3000);
}


// ========================= CHATBOT =========================


// OPEN / CLOSE CHAT

function toggleChat() {

    let chatbot = document.getElementById("chatbot");

    if (chatbot.style.display === "flex") {

        chatbot.style.display = "none";

    } else {

        chatbot.style.display = "flex";
    }
}



// SEND MESSAGE

async function sendMessage() {

    let input = document.getElementById("userInput");

    let message = input.value;

    // Empty message stop
    if (message.trim() === "") {
        return;
    }

    let chatArea = document.getElementById("chatArea");

    // USER MESSAGE

    chatArea.innerHTML += `
        <div class="user-message">
            ${message}
        </div>
    `;

    // Scroll down
    chatArea.scrollTop = chatArea.scrollHeight;

    // Clear input
    input.value = "";

    try {

        // API Request

        const response = await fetch("http://127.0.0.1:5000/chat", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                message: message
            })

        });

        const data = await response.json();

        // BOT MESSAGE

        chatArea.innerHTML += `
            <div class="bot-message">
                ${data.reply}
            </div>
        `;

    } catch (error) {

        // Error Message

        chatArea.innerHTML += `
            <div class="bot-message">
                Server not responding.
            </div>
        `;
    }

    // Auto scroll bottom

    chatArea.scrollTop = chatArea.scrollHeight;
}



// ========================= ENTER KEY SUPPORT =========================

document.addEventListener("DOMContentLoaded", () => {

    let input = document.getElementById("userInput");

    if (input) {

        input.addEventListener("keypress", function (e) {

            if (e.key === "Enter") {

                sendMessage();
            }
        });
    }
});