// Анимация карусель
const carousel = document.querySelector('.carousel');
const items = document.querySelector('.hash');
let currentIndex = 0;

function moveCarousel() {
    currentIndex++;
    if (currentIndex > 9) {
        currentIndex = 0;
    }

    const offset = -(currentIndex * 100 / 10);
    items.style.transform = 'translateX(' + offset + '%)';
}

setInterval(moveCarousel, 2000);

// Часто задаваемые вопросы
document.querySelectorAll('.faq-question').forEach(item => {
    item.addEventListener('click', () => {
        let parent = item.parentNode;
        parent.classList.toggle('active');
        let icon = item.querySelector('.faq-icon');
        icon.textContent = parent.classList.contains('active') ? '-' : '+';
    });
});