function loadinganimation(){
    gsap.from("#content h1",{
        y:30,
        opacity: 0,
        delay: 0.5,
        duration: 0.8,
        stagger: 0.2
    })
    gsap.from("#links h4",{
        y:-30,
        opacity: 0,
        delay: 0.5,
        duration: 1,
        stagger: 0.2
    })
    gsap.from("#nav",{
        y:-30,
        opacity: 0,
        delay: 0.5,
        duration: 1,
        stagger: 0.2
    })
}
loadinganimation()
