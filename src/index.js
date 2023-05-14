import { convertCode } from "./lib/format";
import { infiniteScroll } from "./lib/scroll";

const DEVMODE = false;

const state = {

}

const functionMain = (e) => {
    infiniteScroll();

    // GLOBAL CLICK EVENT LISTENER
    document.addEventListener('click', (event) => {
        // Bind code copy buttons
        if (event.target.matches('.code-copy-icon')) {
            const code = event.target.parentElement.parentElement.querySelector('pre code');

            if (code) {
                let data = code.innerText;
                data = convertCode(data);
                navigator.clipboard.writeText(data);

                // Visual feedback
                event.target.classList.remove('fa-copy');
                event.target.classList.add('fa-check-circle');
                event.target.classList.add('text-green-500');
                setTimeout(() => {
                    event.target.classList.remove('fa-check-circle');
                    event.target.classList.remove('text-green-500');
                    event.target.classList.add('fa-copy');
                }, 500);
            }
        }
    });

    document.querySelectorAll('.btn-sidebar-open').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.add('visible');
        });
    });

    document.querySelectorAll('.btn-sidebar-close').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.remove('visible');
        });
    });

    document.querySelectorAll('.btn-search-open').forEach(button => {
        button.addEventListener('click', () => {
            loadUrl('/search');
        });
    });

    document.querySelectorAll('.blog-post-image').forEach(img => {
        img.addEventListener('click', () => {
            const src = img.getAttribute('data-original');
            const viewer = document.getElementById('image-viewer');
            document.getElementById('image-viewer-img').setAttribute('src', src);
            viewer.classList.remove('hidden');
            viewer.classList.add('flex');
        });
    });

    document.querySelectorAll('.btn-close-image-viewer').forEach(button => {
        button.addEventListener('click', () => {
            const viewer = document.getElementById('image-viewer');
            viewer.classList.remove('flex');
            viewer.classList.add('hidden');
        });
    });

    if (DEVMODE) {
        setInterval(() => {
            hotReload();
        }, 1000);
    }
}

const hotReload = async () => {
    const result = await fetch('/version');
    const version = await result.text();
    if (version === 'reload') {
        console.log('New page version detected, reloading...');
        window.location.reload();
    }
}

if (window.attachEvent) {
    window.attachEvent('onload', functionMain);
} else {
    if (window.onload) {
        var curronload = window.onload;
        var newonload = function (evt) {
            curronload(evt);
            functionMain(evt);
        };
        window.onload = newonload;
    } else {
        window.onload = functionMain;
    }
}