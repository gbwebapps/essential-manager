/* Import delle utility risalendo di un livello */
import { urlbase, apiFetch, showAlert, askConfirm, smoothReplace } from '../frontend.js';

export class GalleryOneManager {
    constructor(config = {}) {

        if (GalleryOneManager.instance) {
            return GalleryOneManager.instance;
        }
        GalleryOneManager.instance = this;

        this.showGalleryUrl = config.showGalleryUrl ?? urlbase + 'account/galleryOne/showGallery';
        this.deleteImageUrl = config.deleteImageUrl ?? urlbase + 'account/galleryOne/deleteImage';
        this.removeCoverUrl = config.removeCoverUrl ?? urlbase + 'account/galleryOne/removeCover';
        this.setCoverUrl = config.setCoverUrl ?? urlbase + 'account/galleryOne/setCover';
        
        this.containerId = '#imagesData';
        /* Rimosso this.container per evitare nodi orfani (fantasma) */
        
        this.onSubmit = this.onSubmit.bind(this);
        this.onClick = this.onClick.bind(this);
        
        this.eventsBound = false;
        this.bindEvents();
    }

    bindEvents() {
        if (this.eventsBound) return;
        this.eventsBound = true;

        document.addEventListener('submit', this.onSubmit);
        document.addEventListener('click', this.onClick);
    }

    async onClick(e) {
        const btn = e.target.closest('.galleryOneAction');
        if (!btn) return;

        e.preventDefault();

        const wrapper = btn.closest('.preview-item');
        if ( ! wrapper) return;

        const formData = new FormData();
        ['id', 'uuid', 'entity', 'context', 'filename'].forEach(key => {
            formData.append(key, wrapper.dataset[key]);
        });

        const ok = await askConfirm(btn.dataset.message);
        if (ok) {
            this.handleAction(btn.dataset.action, formData);
        }
    }

    onSubmit(e) {
        if (e.target.matches('#getImages')) {
            e.preventDefault();
            const formData = new FormData(e.target);
            this.refresh(formData, e.submitter);
        }
    }

    async refresh(formData, loader) {
        try {
            const response = await apiFetch(this.showGalleryUrl, {
                method: 'POST',
                body: formData,
                loader
            });
            const data = await response.json();
            this.handleResponse(data, false);
        } catch (err) {
            console.error(err);
        }
    }

    async handleAction(action, formData) {
        try {
            const actionUrls = {
                deleteImage: this.deleteImageUrl,
                removeCover: this.removeCoverUrl,
                setCover: this.setCoverUrl
            };

            if ( ! actionUrls[action]) return;

            const response = await apiFetch(actionUrls[action], {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            this.handleResponse(data, true);
        } catch (err) {
            console.error(err);
        }
    }

    handleResponse(data, showSuccess) {
        if (data.result === false) {
            showAlert('danger', data.message);
            return;
        }

        if (data.result === true) {
            if (Object.hasOwn(data, 'avatarUrl')) this.updateAvatar(data.avatarUrl);
            if (showSuccess) {
                showAlert('success', data.message);
            }
            
            /* Peschiamo il nodo VIVO dal DOM un attimo prima di agire */
            const currentContainer = document.querySelector(this.containerId);
            if (currentContainer) {
                smoothReplace(currentContainer, data.output);
            }
        }
    }

    /** Aggiorna la foto del menu dopo il cambio o la rimozione della copertina. */
    updateAvatar(url) {
        const avatar = document.querySelector('[data-account-avatar]');
        const placeholder = document.querySelector('[data-account-avatar-placeholder]');
        if (!avatar || !placeholder) return;

        if (url) avatar.src = url;
        else avatar.removeAttribute('src');

        avatar.classList.toggle('d-none', !url);
        placeholder.classList.toggle('d-none', Boolean(url));
    }

    destroy() {
        document.removeEventListener('submit', this.onSubmit);
        document.removeEventListener('click', this.onClick);
    }
}
