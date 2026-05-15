import './bootstrap';
import {
	STEGO_PUBLIC_HEHE_IMAGE_PATH,
	createCoverImageLike,
	hideUserDataInImageLike,
	revealUserDataFromImageLike,
	imageLikeToPngBase64,
	pngBase64ToImageLike,
	stegCapacity,
	embedServiceMetadataPngBase64,
	embedClosedDateMetadataPngBase64,
	revealMetadataJsonFromSteganoPngBase64,
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

function normalizeDayString(value) {
	if (value == null || value === '') return '';
	if (typeof value === 'string') return value.includes('T') ? value.split('T')[0] : value;
	if (typeof value === 'object' && 'format' in value && typeof value.format === 'function') {
		try {
			return value.format('Y-m-d');
		} catch {
			return String(value);
		}
	}
	return String(value);
}

async function regenServiceMetadataStego(livewire) {
	const el = document.getElementById('service-metadata-stego-ui');
	const galleryUrl = (el?.dataset.galleryUrl || '').trim();
	const heheUrl =
		(el?.dataset.heheUrl || '').trim() || new URL(STEGO_PUBLIC_HEHE_IMAGE_PATH, window.location.origin).href;
	const d = livewire.data;
	const useHehe = Boolean(d.use_hehe_for_stego_carrier);
	const carrierAbsoluteUrl = useHehe || !galleryUrl ? heheUrl : galleryUrl;
	const nearestNeighbor = useHehe || !galleryUrl;

	const png = await embedServiceMetadataPngBase64({
		carrierAbsoluteUrl,
		nearestNeighbor,
		formSnapshot: {
			serviceId: livewire.record?.id ?? d.id ?? null,
			name: d.name,
			description: d.description ?? '',
			price: d.price,
			durationMinutes: d.duration_minutes,
			imagePath: d.image ?? '',
			galleryName: el?.dataset.galleryName ?? '',
			galleryImagePath: el?.dataset.galleryImagePath ?? '',
		},
	});

	await livewire.set('data.metadata_stego_png_base64', png);
}

async function regenClosedDateMetadataStego(livewire) {
	const el = document.getElementById('closed-date-metadata-stego-ui');
	const heheUrl =
		(el?.dataset.heheUrl || '').trim() || new URL(STEGO_PUBLIC_HEHE_IMAGE_PATH, window.location.origin).href;
	const d = livewire.data;

	const png = await embedClosedDateMetadataPngBase64({
		carrierAbsoluteUrl: heheUrl,
		nearestNeighbor: true,
		formSnapshot: {
			closedDateId: livewire.record?.id ?? d.id ?? null,
			date: normalizeDayString(d.date),
			type: d.type ?? '',
			note: d.note ?? '',
		},
	});

	await livewire.set('data.metadata_stego_png_base64', png);
}

async function generateClosedDateMetadataPng({ closedDateId, date, type, note }) {
	const heheUrl = new URL(STEGO_PUBLIC_HEHE_IMAGE_PATH, window.location.origin).href;

	return embedClosedDateMetadataPngBase64({
		carrierAbsoluteUrl: heheUrl,
		nearestNeighbor: true,
		formSnapshot: {
			closedDateId: closedDateId ?? null,
			date: normalizeDayString(date),
			type: type ?? '',
			note: note ?? '',
		},
	});
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
		{ threshold: 0.2 },
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
	regenServiceMetadataStego,
	regenClosedDateMetadataStego,
	generateClosedDateMetadataPng,
	revealMetadataJsonFromSteganoPngBase64,
};
