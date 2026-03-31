<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tu Carrito | Pet Palace</title>

    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="../css/bootstrap.min.css">

    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #fcfcfc; }
        .header-ctn > div > a { width: 70px !important; display: block; }
        .header-ctn > div + div { margin-left: 10px !important; }

        .cart-table-wrapper { background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 50px; }
        .table-cart { width: 100%; border-collapse: collapse; }
        .table-cart th { font-size: 13px; text-transform: uppercase; color: #8D99AE; padding-bottom: 15px; border-bottom: 2px solid #E4E7ED; }
        .table-cart td { padding: 20px 10px; vertical-align: middle; border-bottom: 1px solid #f0f0f0; }
        
        .cart-item-img { width: 80px; height: 80px; border: 1px solid #eee; border-radius: 8px; display: flex; justify-content: center; align-items: center; background: #fff; }
        .cart-item-img img { max-width: 90%; max-height: 90%; object-fit: contain; }
        .cart-item-name { font-weight: 700; color: #2B2D42; font-size: 15px; margin: 0; }
        
        .qty-control { display: flex; align-items: center; gap: 10px; }
        .qty-btn { background: #f0f2f5; border: none; width: 30px; height: 30px; border-radius: 50%; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .qty-btn:hover { background: #D10024; color: #fff; }
        
        .btn-delete-item { color: #D10024; background: none; border: none; font-size: 18px; cursor: pointer; transition: 0.2s; }
        .btn-delete-item:hover { transform: scale(1.2); }

        .cart-summary-box { background: #F8F9FA; border-radius: 8px; padding: 25px; border: 1px solid #E4E7ED; }
        .cart-summary-box h3 { font-size: 18px; text-transform: uppercase; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; font-weight: 500; color: #2B2D42; }
        .summary-total { font-size: 22px; font-weight: 800; color: #D10024; border-top: 2px solid #E4E7ED; padding-top: 15px; margin-top: 15px; }

        .btn-checkout { background: #D10024; color: white; border: none; padding: 15px; border-radius: 8px; font-weight: 700; width: 100%; font-size: 16px; margin-top: 20px; transition: 0.3s; text-transform: uppercase; }
        .btn-checkout:hover { background: #15161D; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

        /* WIDGET CHAT */
        .qty-msg { position: absolute; top: -10px; right: 15px; width: 18px; height: 18px; line-height: 18px; text-align: center; border-radius: 50%; font-size: 10px; color: #FFF; background-color: #D10024; display: none; border: 2px solid #15161D; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
        #chat-widget { position: fixed; bottom: 25px; right: 25px; width: 380px; height: 600px; background: #ffffff; border-radius: 20px; box-shadow: 0 12px 28px 0 rgba(0, 0, 0, 0.2), 0 2px 4px 0 rgba(0, 0, 0, 0.1); z-index: 10000; display: none; flex-direction: column; overflow: hidden; font-family: -apple-system, sans-serif; border: 1px solid rgba(0,0,0,0.05); transition: all 0.3s ease; }
        .chat-header { background: #ffffff; padding: 15px 20px; height: 75px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f0f0f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); cursor: pointer; }
        .chat-header-left { display: flex; align-items: center; gap: 15px; }
        .chat-back-btn { font-size: 18px; color: #333; cursor: pointer; display: none; padding: 8px; border-radius: 50%; transition: 0.2s; }
        .chat-back-btn:hover { background: #f5f5f5; }
        .chat-avatar-wrapper { position: relative; }
        .chat-avatar-header { width: 45px; height: 45px; background: linear-gradient(135deg, #D10024, #ff4d4d); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 2px 5px rgba(209, 0, 36, 0.3); }
        .status-dot { position: absolute; bottom: 2px; right: 2px; width: 12px; height: 12px; background: #31a24c; border: 2px solid #fff; border-radius: 50%; }
        .chat-info h4 { margin: 0; color: #1c1e21; font-size: 17px; font-weight: 700; letter-spacing: -0.3px; }
        .chat-info small { font-size: 13px; color: #65676b; }
        .close-chat { font-size: 24px; color: #bcc0c4; cursor: pointer; transition: 0.2s; padding: 5px; }
        .close-chat:hover { color: #D10024; }
        .chat-body { flex-grow: 1; background-color: #fff; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 2px; }
        .message-row { display: flex; margin-bottom: 8px; width: 100%; }
        .message-content-wrapper { max-width: 75%; display: flex; flex-direction: column; }
        .message-row.incoming { justify-content: flex-start; }
        .message-row.incoming .message-bubble { background-color: #f0f2f5; color: #050505; border-bottom-left-radius: 4px; }
        .message-row.incoming .msg-name-label { display: block; font-size: 11px; color: #65676b; margin-left: 12px; margin-bottom: 2px; }
        .message-row.outgoing { justify-content: flex-end; }
        .message-row.outgoing .message-bubble { background-color: #D10024; color: #ffffff; border-bottom-right-radius: 4px; }
        .message-bubble { padding: 8px 12px; border-radius: 18px; font-size: 15px; line-height: 1.4; position: relative; word-wrap: break-word; box-shadow: 0 1px 2px rgba(0,0,0,0.1); animation: fadeIn 0.3s ease; display: inline-block; min-width: 60px; }
        .msg-time { font-size: 10px; margin-top: 5px; margin-left: 8px; float: right; white-space: nowrap; vertical-align: bottom; line-height: 1; }
        .chat-footer { padding: 15px; background: #fff; border-top: 1px solid #f0f0f0; display: none; align-items: center; gap: 10px; }
        .chat-input-wrapper { flex: 1; background: #f0f2f5; border-radius: 20px; padding: 8px 15px; display: flex; align-items: center; }
        .chat-input { width: 100%; background: transparent; border: none; outline: none; font-size: 15px; color: #050505; }
        .btn-chat-send { color: #D10024; background: none; border: none; font-size: 20px; cursor: pointer; transition: 0.2s; padding: 5px; }
        .inbox-list { list-style: none; padding: 0; margin: 0; }
        .inbox-item { padding: 15px; display: flex; align-items: center; cursor: pointer; border-radius: 10px; transition: 0.2s; }
        .inbox-item:hover { background-color: #f2f2f2; }
        .inbox-avatar { width: 50px; height: 50px; background: #D10024; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-right: 15px; }
        .inbox-details { flex: 1; overflow: hidden; }
        .inbox-top { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .inbox-name { font-weight: 600; font-size: 16px; color: #050505; }
        .inbox-time { font-size: 12px; color: #65676b; }
        .inbox-msg { font-size: 14px; color: #65676b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .unread-dot { width: 10px; height: 10px; background: #D10024; border-radius: 50%; margin-left: 10px; }
    </style>
</head>

<body>

    <header>
        <div id="top-header">
            <div class="container">
                <ul class="header-links pull-left">
                    <li><a><i class="fa fa-phone"></i> +52 449 928 9336</a></li>
                    <li><a href="https://mail.google.com/"><i class="fa fa-envelope-o"></i> petspalace@gmail.com</a></li>
                    <li><a href="#"><i class="fa fa-map-marker"></i> Av. Adolfo López Mateos </a></li>
                </ul>
                <ul class="header-links pull-right">
                    <li id="auth-container">
                        <a href="../login.html" style="font-weight: bold; color: #4ea3f1; margin-right: 10px;"><i class="fa fa-sign-in"></i> Iniciar sesión</a>
                    </li>
                </ul>
            </div>
        </div>

        <div id="header">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="header-logo">
                            <a href="../index.html" class="logo"><img src="../img/logo6.png" alt="Pet Palace"></a>
                        </div>
                    </div>
                    
                    <div class="col-md-6"></div>

                    <div class="col-md-3 clearfix">
                        <div class="header-ctn">
                            <div id="msg-wrapper" style="display:none; position: relative;">
                                <a href="#" onclick="toggleChatWidget(event)">
                                    <i class="fa fa-envelope"></i>
                                    <span>Mensajes</span>
                                    <div class="qty-msg" id="msg-badge">0</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <nav id="navigation">
        <div class="container">
            <div id="responsive-nav">
                <ul class="main-nav nav navbar-nav">
                    <li><a href="../index.html">Inicio</a></li>
                    <li><a href="../general.html">General</a></li>
                    <li><a href="../offers.html">Ofertas</a></li>
                    <li><a href="../dogs.html">Perros</a></li>
                    <li><a href="../cats.html">Gatos</a></li>
                    <li><a href="../rodents.html">Roedores</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div id="breadcrumb" class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul class="breadcrumb-tree">
                        <li><a href="../index.html">Inicio</a></li>
                        <li class="active">Carrito de Compras</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section" style="min-height: 50vh;">
        <div class="container">
            <div class="row" id="cart-content-area">
                
                <div class="col-md-8">
                    <div class="cart-table-wrapper">
                        <h3 style="margin-top:0; font-weight:800; color:#15161D;"><i class="fa fa-shopping-bag text-danger"></i> Tus Productos</h3>
                        <hr>
                        <div class="table-responsive">
                            <table class="table-cart">
                                <thead>
                                    <tr>
                                        <th colspan="2">Producto</th>
                                        <th>Precio Unitario</th>
                                        <th class="text-center">Cantidad</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="cart-table-body">
                                    <tr><td colspan="6" class="text-center py-5"><i class="fa fa-spinner fa-spin"></i> Cargando tu carrito...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="cart-summary-box">
                        <h3>Resumen del Pedido</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal (<span id="sum-items-count">0</span> items)</span>
                            <span id="sum-subtotal">$0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Envío</span>
                            <span style="color:#28a745;">Gratis</span>
                        </div>
                        
                        <div class="summary-row summary-total">
                            <span>TOTAL</span>
                            <span id="sum-total">$0.00</span>
                        </div>

                        <button class="btn-checkout" id="btn-proceder-pago" onclick="window.location.href='../checkout.html'">
                            Proceder al Pago <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="chat-widget">
        <div class="chat-header" onclick="if(currentView==='chat') mostrarBandeja();">
            <div class="chat-header-left">
                <i class="fa fa-arrow-left chat-back-btn" id="chatBackBtn"></i>
                <div class="chat-avatar-wrapper">
                    <div class="chat-avatar-header" id="headerAvatar"><i class="fa fa-headset"></i></div>
                    <div class="status-dot"></div>
                </div>
                <div class="chat-info">
                    <h4 id="chatTitle">Soporte</h4>
                    <small id="chatSubtitle">Haga clic para ver bandeja</small>
                </div>
            </div>
            <i class="fa fa-times close-chat" onclick="toggleChatWidget(event)"></i>
        </div>
        <div class="chat-body" id="chatBody"></div>
        <div class="chat-footer" id="chatFooter">
            <div class="chat-input-wrapper">
                <input type="text" class="chat-input" id="chatInputMain" placeholder="Escribe un mensaje..." autocomplete="off">
            </div>
            <button class="btn-chat-send" onclick="enviarMensajeChat()"><i class="fa fa-paper-plane"></i></button>
        </div>
    </div>

    <footer id="footer">
        <div class="section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-xs-6">
                        <div class="footer">
                            <h3 class="footer-title">Nosotros</h3>
                            <ul class="footer-links">
                                <li><a href="#"><i class="fa fa-map-marker"></i> ITA, Aguascalientes</a></li>
                                <li><a><i class="fa fa-phone"></i> +52 449 928 9336</a></li>
                                <li><a><i class="fa fa-envelope-o"></i> petspalace@gmail.com</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    
    <script>
        let currentUser = null;
        let cart = [];
        let currentView = 'inbox'; 
        let chatPollInterval = null;

        $(document).ready(function() {
            currentUser = JSON.parse(localStorage.getItem('currentUser'));
            
            if (currentUser) {
                $('#auth-container').html(`
                    <a href="../userprofile.html" style="font-weight: bold; color: #4ea3f1; margin-right: 15px;">
                        <i class="fa fa-user"></i> ${currentUser.nombre}
                    </a>
                    <a href="#" onclick="cerrarSesionIndex()" style="font-size:12px; color:#D10024;">(Salir)</a>
                `);

                $('#msg-wrapper').css('display', 'inline-block'); 
                verificarBadge();
                setInterval(verificarBadge, 5000); 

                const savedCart = localStorage.getItem(`cart_${currentUser.id}`);
                if (savedCart) {
                    cart = JSON.parse(savedCart);
                }
                
                renderCartTable();

            } else {
                alert("Debes iniciar sesión para ver tu carrito.");
                window.location.href = "../login.html";
            }
        });

        // =================================================================
        // LÓGICA PRINCIPAL DE LA VISTA DEL CARRITO
        // =================================================================
        function renderCartTable() {
            const $tbody = $('#cart-table-body');
            $tbody.empty();
            let totalGeneral = 0;
            let countTotal = 0;

            if (cart.length === 0) {
                $tbody.html(`
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fa fa-shopping-cart text-muted" style="font-size: 40px; margin-bottom: 15px;"></i>
                            <h4 class="text-muted">Tu carrito está vacío</h4>
                            <a href="../general.html" class="btn btn-default mt-3" style="background:#D10024; color:white; border-radius:8px;">Explorar Productos</a>
                        </td>
                    </tr>
                `);
                $('#btn-proceder-pago').attr('disabled', true).css('opacity', '0.5');
            } else {
                $('#btn-proceder-pago').attr('disabled', false).css('opacity', '1');
                
                cart.forEach((prod, index) => {
                    const subtotal = prod.precio * prod.cantidad;
                    totalGeneral += subtotal;
                    countTotal += prod.cantidad;

                    const html = `
                        <tr>
                            <td style="width: 100px;">
                                <div class="cart-item-img">
                                    <img src="../${prod.imagen}" onerror="this.src='../img/logo6.png'" alt="${prod.nombre}">
                                </div>
                            </td>
                            <td><p class="cart-item-name">${prod.nombre}</p></td>
                            <td class="font-weight-bold text-dark">$${parseFloat(prod.precio).toFixed(2)}</td>
                            <td>
                                <div class="qty-control justify-content-center">
                                    <button class="qty-btn" onclick="cambiarCantidad(${index}, -1)"><i class="fa fa-minus"></i></button>
                                    <span style="font-weight:bold; font-size:16px;">${prod.cantidad}</span>
                                    <button class="qty-btn" onclick="cambiarCantidad(${index}, 1)"><i class="fa fa-plus"></i></button>
                                </div>
                            </td>
                            <td class="font-weight-bold" style="color:#D10024; font-size:16px;">$${subtotal.toFixed(2)}</td>
                            <td class="text-center">
                                <button class="btn-delete-item" onclick="eliminarDelCarrito(${index})"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    `;
                    $tbody.append(html);
                });
            }

            $('#sum-items-count').text(countTotal);
            $('#sum-subtotal').text('$' + totalGeneral.toFixed(2));
            $('#sum-total').text('$' + totalGeneral.toFixed(2));
        }

        function cambiarCantidad(index, delta) {
            if(cart[index].cantidad + delta > 0) {
                cart[index].cantidad += delta;
            } else if (cart[index].cantidad + delta === 0) {
                if(confirm("¿Eliminar producto del carrito?")) {
                    cart.splice(index, 1);
                }
            }
            guardarYActualizar();
        }

        function eliminarDelCarrito(index) {
            if(confirm("¿Seguro que deseas eliminar este producto?")) {
                cart.splice(index, 1);
                guardarYActualizar();
            }
        }

        function guardarYActualizar() {
            localStorage.setItem(`cart_${currentUser.id}`, JSON.stringify(cart));
            renderCartTable();
        }

        // =================================================================
        // LÓGICA DEL CHAT
        // =================================================================
        function toggleChatWidget(e) {
            if(e) { e.preventDefault(); e.stopPropagation(); }
            const widget = $('#chat-widget');
            
            if (widget.is(':visible')) {
                widget.css('opacity', '0');
                setTimeout(() => widget.hide(), 300);
                if(chatPollInterval) clearInterval(chatPollInterval);
            } else {
                widget.show();
                setTimeout(() => widget.css('opacity', '1'), 10);
                widget.css('display', 'flex'); 
                mostrarBandeja(); 
            }
        }

        function mostrarBandeja() {
            currentView = 'inbox';
            $('#chatBackBtn').hide();
            $('#headerAvatar').html('<i class="fa fa-comments"></i>');
            $('#chatTitle').text('Mensajes');
            $('#chatSubtitle').text('Bandeja de Entrada');
            $('#chatFooter').hide();
            
            if(chatPollInterval) clearInterval(chatPollInterval);
            cargarBandeja();
            chatPollInterval = setInterval(cargarBandeja, 5000);
        }

        function cargarBandeja() {
            if(!currentUser || currentView !== 'inbox') return;

            $.getJSON('notifications.php', { user_id: currentUser.id }, function(data) {
                const $box = $('#chatBody');
                
                if(data.length === 0) {
                    $box.html('<div class="text-center py-5 text-muted" style="margin-top:50px;">Aún no tienes mensajes.<br><button class="btn btn-default btn-sm mt-3" onclick="entrarAlChat()">Contactar Soporte</button></div>');
                    return;
                }

                const adminMsgs = data.filter(m => m.origen === 'admin');
                const unreadCount = adminMsgs.filter(n => n.estado !== 'leido').length;
                
                adminMsgs.sort((a,b) => new Date(b.fecha) - new Date(a.fecha));
                let senderName = "Soporte Pet Palace"; 
                if (adminMsgs.length > 0 && adminMsgs[0].remitente) {
                    senderName = adminMsgs[0].remitente;
                }

                data.sort((a,b) => new Date(b.fecha) - new Date(a.fecha));
                const lastMsg = data[0];
                const time = lastMsg.fecha.substring(11, 16);
                const preview = (lastMsg.origen === 'cliente' ? 'Tú: ' : '') + lastMsg.mensaje;
                const unreadDot = unreadCount > 0 ? '<div class="unread-dot"></div>' : '';
                const boldStyle = unreadCount > 0 ? 'font-weight:700; color:#000;' : '';

                const html = `
                    <ul class="inbox-list">
                        <li class="inbox-item" onclick="entrarAlChat()">
                            <div class="inbox-avatar"><i class="fa fa-headset"></i></div>
                            <div class="inbox-details">
                                <div class="inbox-top">
                                    <span class="inbox-name">${senderName}</span>
                                    <span class="inbox-time">${time}</span>
                                </div>
                                <div class="inbox-msg" style="${boldStyle}">${preview}</div>
                            </div>
                            ${unreadDot}
                        </li>
                    </ul>`;
                $box.html(html);
            });
        }

        function entrarAlChat() {
            currentView = 'chat';
            $('#chatBackBtn').show();
            $('#chatFooter').css('display', 'flex'); 
            
            cargarConversacion(false);
            if(chatPollInterval) clearInterval(chatPollInterval);
            chatPollInterval = setInterval(() => cargarConversacion(true), 3000);
            marcarTodoLeido();
        }

        function cargarConversacion(silent = false) {
            if(!currentUser || currentView !== 'chat') return;
            const $box = $('#chatBody');
            const isAtBottom = ($box.scrollTop() + $box.innerHeight() >= $box[0].scrollHeight - 60);

            if(!silent) $box.html('<div class="text-center py-5"><i class="fa fa-circle-o-notch fa-spin text-muted"></i></div>');

            $.getJSON('notifications.php', { user_id: currentUser.id }, function(data) {
                let finalTitle = "Soporte Pet Palace";
                const adminMsgs = data.filter(m => m.origen === 'admin');
                adminMsgs.sort((a,b) => new Date(b.fecha) - new Date(a.fecha));
                if (adminMsgs.length > 0 && adminMsgs[0].remitente) finalTitle = adminMsgs[0].remitente;
                
                $('#chatTitle').text(finalTitle);
                $('#chatSubtitle').text('Activo ahora');

                data.sort((a, b) => new Date(a.fecha) - new Date(b.fecha));

                let html = '<div style="margin-top:auto;">'; 
                if(data.length === 0) html += '<div class="text-center text-muted small mt-3">¡Hola! ¿En qué podemos ayudarte?</div>';

                data.forEach(n => {
                    const isMe = (n.origen === 'cliente');
                    const rowClass = isMe ? 'outgoing' : 'incoming';
                    const nameLabel = !isMe ? `<span class="msg-name-label">${n.remitente || 'Soporte'}</span>` : '';

                    html += `
                        <div class="message-row ${rowClass}">
                            <div class="message-content-wrapper">
                                ${nameLabel}
                                <div class="message-bubble">
                                    ${n.mensaje}
                                    <span class="msg-time">${n.fecha.substring(11, 16)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                
                if(silent) {
                    if($box.html() !== html) {
                        $box.html(html);
                        if(isAtBottom) $box.scrollTop($box[0].scrollHeight);
                    }
                } else {
                    $box.html(html);
                    $box.scrollTop($box[0].scrollHeight); 
                }
            });
        }

        function enviarMensajeChat() {
            const txt = $('#chatInputMain').val().trim();
            if(!txt) return;
            
            $('#chatInputMain').val('').focus(); 
            
            const tiempoActual = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const tempMsg = `
                <div class="message-row outgoing">
                    <div class="message-content-wrapper">
                        <div class="message-bubble" style="opacity:0.7;">
                            ${txt}
                            <span class="msg-time">${tiempoActual}</span>
                        </div>
                    </div>
                </div>`;
            $('#chatBody').append(tempMsg);
            $('#chatBody').scrollTop($('#chatBody')[0].scrollHeight);

            $.ajax({
                url: 'notifications.php', 
                type: 'POST', 
                contentType: 'application/json',
                data: JSON.stringify({
                    user_id: currentUser.id, 
                    sender_id: currentUser.id, 
                    message: txt,
                    origen: 'cliente' 
                }),
                success: function(res) { cargarConversacion(true); },
                error: function(err) { alert("No se pudo enviar el mensaje."); }
            });
        }

        $('#chatInputMain').on('keypress', function(e) { 
            if(e.which === 13) { e.preventDefault(); enviarMensajeChat(); }
        });

        function verificarBadge() {
            if(!currentUser) return;
            $.getJSON('notifications.php', { user_id: currentUser.id }, function(data) {
                const unread = data.filter(n => n.estado !== 'leido' && n.origen === 'admin');
                const badge = $('#msg-badge');
                if(unread.length > 0) badge.text(unread.length).show(); else badge.hide();
            });
        }

        function marcarTodoLeido() {
            if(!currentUser) return;
            $.getJSON('notifications.php', { user_id: currentUser.id }, function(data) {
                data.filter(n => n.estado !== 'leido' && n.origen === 'admin').forEach(m => {
                    $.ajax({ url: 'notifications.php', type: 'PUT', contentType: 'application/json', data: JSON.stringify({ id: m.id, estado: 'leido' }) });
                });
                $('#msg-badge').hide();
            });
        }

        function cerrarSesionIndex() {
            localStorage.removeItem('currentUser');
            window.location.href = "../index.html";
        }
    </script>
</body>
</html>