/**
 * PET PALACE — MENÚS LATERALES POR ROL
 * Incluir este archivo en todas las páginas del admin:
 *   <script src="_sidebar_menus.js"></script>
 *
 * Uso:
 *   renderSidebar(rolDelUsuario, 'nombre-pagina-activa.html');
 */

const PP_MENUS = {
    admin: [
        { href: 'admin-panel.html',   icon: 'fa-chart-pie',    label: 'Dashboard' },
        { href: 'clients.html',       icon: 'fa-users',         label: 'Clientes' },
        { href: 'products.html',      icon: 'fa-box-open',      label: 'Inventario' },
        { href: 'orders.html',        icon: 'fa-shopping-bag',  label: 'Pedidos' },
        { href: 'providers.html',     icon: 'fa-truck',         label: 'Proveedores' },
        { href: 'movimientos.html',   icon: 'fa-history',       label: 'Movimientos' },
        { href: 'erp-dashboard.html', icon: 'fa-building',      label: 'Dashboard ERP' },
        { href: 'erp-ordenes.html',   icon: 'fa-file-invoice',  label: 'Órdenes ERP' },
        { href: 'erp-roles.html',     icon: 'fa-user-shield',   label: 'Roles ERP' },
        { divider: true },
        { href: '../index.html',      icon: 'fa-store',         label: 'Ver Tienda' },
        { href: '#',                  icon: 'fa-sign-out-alt',  label: 'Cerrar Sesión', onclick: 'cerrarSesion()' }
    ],
    vendedor: [
        { href: 'admin-panel.html',   icon: 'fa-chart-pie',    label: 'Dashboard' },
        { href: 'clients.html',       icon: 'fa-users',         label: 'Clientes' },
        { href: 'orders.html',        icon: 'fa-shopping-bag',  label: 'Pedidos' },
        { href: 'erp-dashboard.html', icon: 'fa-building',      label: 'Dashboard ERP' },
        { href: 'erp-ordenes.html',   icon: 'fa-file-invoice',  label: 'Órdenes ERP' },
        { href: 'erp-roles.html',     icon: 'fa-user-shield',   label: 'Roles ERP' },
        { divider: true },
        { href: '../index.html',      icon: 'fa-store',         label: 'Ver Tienda' },
        { href: '#',                  icon: 'fa-sign-out-alt',  label: 'Cerrar Sesión', onclick: 'cerrarSesion()' }
    ],
    soporte: [
        { href: 'admin-panel.html',   icon: 'fa-chart-pie',    label: 'Dashboard' },
        { href: 'clients.html',       icon: 'fa-users',         label: 'Clientes' },
        { href: 'erp-roles.html',     icon: 'fa-user-shield',   label: 'Roles ERP' },
        { divider: true },
        { href: '../index.html',      icon: 'fa-store',         label: 'Ver Tienda' },
        { href: '#',                  icon: 'fa-sign-out-alt',  label: 'Cerrar Sesión', onclick: 'cerrarSesion()' }
    ],
    // ★ LOGÍSTICA: exactamente 4 páginas ERP + cerrar sesión
    logistica: [
        { href: 'erp-dashboard.html', icon: 'fa-building',      label: 'Dashboard ERP' },
        { href: 'erp-logistica.html', icon: 'fa-truck-loading', label: 'Panel Logístico' },
        { href: 'erp-procesos.html',  icon: 'fa-cogs',          label: 'Procesos' },
        { href: 'erp-recursos.html',  icon: 'fa-users-cog',     label: 'Recursos' },
        { divider: true },
        { href: '../index.html',      icon: 'fa-store',         label: 'Ver Tienda' },
        { href: '#',                  icon: 'fa-sign-out-alt',  label: 'Cerrar Sesión', onclick: 'cerrarSesion()' }
    ]
};

/**
 * Renderiza el menú lateral.
 * @param {string} rol          - rol real del usuario logueado
 * @param {string} paginaActual - nombre del archivo HTML activo (ej. 'erp-logistica.html')
 */
function renderSidebarGlobal(rol, paginaActual) {
    const menu = PP_MENUS[rol] || PP_MENUS['admin'];
    const ul = document.getElementById('sidebarMenu');
    if (!ul) return;
    ul.innerHTML = '';
    menu.forEach(item => {
        if (item.divider) { ul.innerHTML += '<hr>'; return; }
        const isActive = paginaActual && item.href === paginaActual ? 'active' : '';
        const onclick  = item.onclick ? `onclick="${item.onclick}"` : '';
        ul.innerHTML += `<li class="${isActive}"><a href="${item.href}" ${onclick}><i class="fa ${item.icon}"></i> ${item.label}</a></li>`;
    });
}