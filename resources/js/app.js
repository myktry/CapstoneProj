import './bootstrap';
import {
	createCoverImageLike,
	hideUserDataInImageLike,
	revealUserDataFromImageLike,
	imageLikeToPngBase64,
	pngBase64ToImageLike,
	stegCapacity,
} from './stego/index.js';

if (!window.__livewireLoadingEnhancementsBound) {
	window.__livewireLoadingEnhancementsBound = true;

	const setLivewireNavigating = (isNavigating) => {
		document.body.classList.toggle('is-livewire-navigating', isNavigating);
	};

	document.addEventListener('livewire:navigating', () => setLivewireNavigating(true));
	document.addEventListener('livewire:navigated', () => setLivewireNavigating(false));
	document.addEventListener('livewire:init', () => setLivewireNavigating(false));
}

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
