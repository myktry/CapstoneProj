import './bootstrap';
import {
	createCoverImageLike,
	hideUserDataInImageLike,
	revealUserDataFromImageLike,
	imageLikeToPngBase64,
	pngBase64ToImageLike,
	stegCapacity,
} from './stego/index.js';

const animatedElements = document.querySelectorAll('[data-animate]');

if (animatedElements.length) {
	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					observer.unobserve(entry.target);
				}
			});
		},
		{ threshold: 0.2 }
	);

	animatedElements.forEach((el) => observer.observe(el));
}

window.StegoDemo = {
	createCoverImageLike,
	hideUserDataInImageLike,
	revealUserDataFromImageLike,
	imageLikeToPngBase64,
	pngBase64ToImageLike,
	stegCapacity,
};

// Start Alpine after Livewire scripts are loaded (Livewire registers Alpine plugin).
if (window.Alpine && typeof window.Alpine.start === 'function') {
	window.Alpine.start();
}
