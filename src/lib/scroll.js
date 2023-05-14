export function infiniteScroll() {
    let currentIndex = 9;
    let scrollDone = false;
    const targetElement = document.getElementById('post-list-bottom');
    window.addEventListener('scroll', () => {
        if (!scrollDone) {
            const targetRect = targetElement.getBoundingClientRect();
            const targetTop = targetRect.top;
            const targetHeight = targetRect.height;
            const scrollHeight = document.documentElement.scrollHeight;
            const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            const clientHeight = document.documentElement.clientHeight;
            const distanceFromBottom = scrollHeight - scrollTop - clientHeight;
            const targetTotal = targetTop + targetHeight;
            //console.log(targetTotal, clientHeight)
            if (targetTop >= 0 && targetTotal <= clientHeight) {
                currentIndex += 3;
                fetch(`/fetch?index=${currentIndex}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            const contentElement = document.getElementById('post-sidebar-cont');
                            const newContent = data.join('');
                            contentElement.innerHTML += newContent;

                            const script = document.createElement('script');
                            script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8001907419576129';
                            script.crossOrigin = 'anonymous';

                            contentElement.appendChild(script);

                            (adsbygoogle = window.adsbygoogle || []).push({});
                        } else {
                            scrollDone = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading data:', error);
                    });
            }
        }
    });
}