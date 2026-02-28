import './bootstrap';

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
