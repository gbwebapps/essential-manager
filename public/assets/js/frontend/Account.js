/* Import delle costanti e utility da backend.js */
import { action, urlbase } from './frontend.js';

/* Import dei componenti dalla sottocartella */
import { EditManager, GetTokensManager, DeleteTokenManager, ResetPasswordManager, SecurityManager, DeleteAccountManager } from './modules/Account.js';

/* Import componenti immagini */
import { UploadPreviewImgManager } from './components/UploadPreview.js';
import { GalleryOneManager } from './components/GalleryOne.js';

const actions = {
    index: function() {}, 
    general: function() {}, 
    edit: function() {

    	const editManager = new EditManager();
        editManager.init();

	}, 

    images: function() {

        const galleryOneManager = new GalleryOneManager({
            showGalleryUrl: urlbase + 'account/galleryOne/showGallery',
            deleteImageUrl: urlbase + 'account/galleryOne/deleteImage',
            removeCoverUrl: urlbase + 'account/galleryOne/removeCover',
            setCoverUrl: urlbase + 'account/galleryOne/setCover'
        });
        new UploadPreviewImgManager(galleryOneManager, {
            saveImagesUrl: urlbase + 'account/uploadPreview/saveImages'
        });

    }, 
    tokens: function() {

        const tokensManager = new GetTokensManager();
        tokensManager.init();

        const deleteTokenManager = new DeleteTokenManager();
        deleteTokenManager.init();
        
    }, 
    resetPassword: function() {

        const resetPasswordManager = new ResetPasswordManager();
        resetPasswordManager.init();

    },
    deleteAccount: function() {
        new DeleteAccountManager().init();
    },
    security: function() {

        const securityManager = new SecurityManager();
        securityManager.init();

    }
};

/* Se esiste una funzione per l'azione corrente, eseguila */
if (actions[action]) {
    actions[action]();
} else {
    console.error("Azione non definita:", action);
}

