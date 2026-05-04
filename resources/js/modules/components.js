// resources/js/modules/components.js

/**
 * Inicializa todos los componentes JS de la página.
 * Añadir aquí la inicialización de cada módulo.
 */
export const initComponents = () => {
    initToggles();
};

/**
 * Ejemplo: toggles de visibilidad
 */
const initToggles = () => {
    document.querySelectorAll('[data-toggle]').forEach((trigger) => {
        trigger.addEventListener('click', (e) => {
            const targetId = e.currentTarget.dataset.toggle;
            const target = document.getElementById(targetId);

            if (target) {
                target.classList.toggle('hidden');
            }
        });
    });
};
