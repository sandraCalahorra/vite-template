<template>
<div class="flex h-screen w-full bg-slate-50 items-center justify-center p-6">
    <div class="w-full max-w-md bg-white p-12 rounded-2xl shadow-lg flex flex-col items-center text-center space-y-6">
        <img src="/img/template.png" class="h-20 mb-2"/>
        <div>
            <h1 class="text-4xl font-bold text-primary mb-2">404</h1>
            <h2 class="text-xl font-semibold text-slate-800 uppercase tracking-wide">Página no encontrada</h2>
            <p class="text-sm text-slate-500 mt-4 leading-relaxed">
                Lo sentimos, no hemos podido encontrar la página que buscas.
                Asegúrate de que la dirección es correcta o vuelve al inicio.
            </p>
        </div>
        <div class="w-full pt-4">
            <ButtonRedirect
                :to="{ 'name': 'home' }"
                text="VOLVER AL INICIO"
                :tag="false"
                class="w-full py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-primary-light"
            />
        </div>
        <div class="pt-4 border-t border-gray-100 w-full">
            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-medium">
                Template
            </p>
        </div>
    </div>
</div>
</template>
