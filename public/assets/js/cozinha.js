/**
 * TOTEM LUXX - Cozinha JS
 * Kanban de pedidos para cozinha
 */

// Estado
let pedidos = [];
const DESTINO = 'cozinha';

// Dados de exemplo
const pedidosDemo = [
    { id: 72, codigo: 'A-45', status: 'pendente', tempo: 0, itens: [
        { nome: 'Porção de Batata', qtd: 1 },
        { nome: 'Asa de Frango', qtd: 2 }
    ]},
    { id: 73, codigo: 'A-46', status: 'pendente', tempo: 0, itens: [
        { nome: 'X-Bacon', qtd: 1 }
    ]},
    { id: 70, codigo: 'A-43', status: 'preparando', tempo: 245, itens: [
        { nome: 'Pizza Marguerita', qtd: 1 }
    ]},
    { id: 68, codigo: 'A-41', status: 'pronto', tempo: 480, itens: [
        { nome: 'Hambúrguer Artesanal', qtd: 2 }
    ]}
];

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    pedidos = pedidosDemo;
    renderCards();
    setInterval(updateTimes, 1000);
    // Em produção: setInterval(fetchPedidos, 5000);
});

// Renderizar cards
function renderCards() {
    const cols = {
        pendente: document.getElementById('columnNovos'),
        preparando: document.getElementById('columnPreparando'),
        pronto: document.getElementById('columnPronto')
    };
    
    Object.values(cols).forEach(c => c.innerHTML = '');
    const counts = { pendente: 0, preparando: 0, pronto: 0 };
    
    pedidos.forEach(p => {
        counts[p.status]++;
        cols[p.status].appendChild(createCard(p));
    });
    
    document.getElementById('countNovos').textContent = counts.pendente;
    document.getElementById('countPreparando').textContent = counts.preparando;
    document.getElementById('countPronto').textContent = counts.pronto;
    
    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('pt-BR');
}

// Criar card
function createCard(p) {
    const div = document.createElement('div');
    div.className = 'order-card' + (p.status === 'pronto' ? ' ready' : '');
    
    const mins = Math.floor(p.tempo / 60);
    const secs = p.tempo % 60;
    const time = `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
    
    let btnClass = 'card-btn ';
    let btnText = '';
    
    if (p.status === 'pendente') {
        btnClass += 'primary';
        btnText = '▶ Iniciar Preparo';
    } else if (p.status === 'preparando') {
        btnClass += 'success';
        btnText = '✓ Marcar Pronto';
    } else {
        btnClass += 'disabled';
        btnText = '✓ Aguardando';
    }
    
    div.innerHTML = `
        <div class="card-header">
            <div>
                <small style="color:#64748b">Pedido</small>
                <div style="font-size:18px;font-weight:700">#${p.id}</div>
            </div>
            <div style="text-align:right">
                <span style="background:rgba(139,92,246,0.2);color:#a78bfa;padding:2px 8px;border-radius:6px;font-size:12px">${p.codigo}</span>
                <div style="font-size:12px;color:#64748b;margin-top:4px">⏱️ ${time}</div>
            </div>
        </div>
        <div class="card-items">
            ${p.itens.map(i => `<div class="card-item"><span class="card-item-qty">${i.qtd}x</span> ${i.nome}</div>`).join('')}
        </div>
        <button class="${btnClass}" onclick="moveCard(${p.id})" ${p.status === 'pronto' ? 'disabled' : ''}>${btnText}</button>
    `;
    
    return div;
}

// Mover card
function moveCard(id) {
    const p = pedidos.find(x => x.id === id);
    if (!p) return;
    
    if (p.status === 'pendente') {
        p.status = 'preparando';
        updateStatus(id, 'preparando');
    } else if (p.status === 'preparando') {
        p.status = 'pronto';
        updateStatus(id, 'pronto');
    }
    
    renderCards();
}

// Atualizar status na API
function updateStatus(id, status) {
    fetch('/api/pedidos', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_status', pedido_id: id, status: status, destino: DESTINO })
    }).catch(console.error);
}

// Atualizar tempos
function updateTimes() {
    pedidos.forEach(p => { if (p.status !== 'pronto') p.tempo++; });
    renderCards();
}

// Buscar pedidos da API
function fetchPedidos() {
    fetch(`/api/pedidos?destino=${DESTINO}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                pedidos = data.pedidos;
                renderCards();
            }
        })
        .catch(console.error);
}

// Modal de saída
function openExitModal() {
    document.getElementById('exitModal').classList.remove('hidden');
    document.getElementById('pinInput').focus();
}

function closeExitModal() {
    document.getElementById('exitModal').classList.add('hidden');
    document.getElementById('pinInput').value = '';
    document.getElementById('pinError').classList.add('hidden');
}

function handleExit(e) {
    e.preventDefault();
    const pin = document.getElementById('pinInput').value;
    
    fetch('/api/auth', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'verify_pin', pin: pin })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/modulos';
        } else {
            showPinError();
        }
    })
    .catch(() => {
        // Demo: PIN 1234
        if (pin === '1234') {
            window.location.href = '/modulos';
        } else {
            showPinError();
        }
    });
    
    return false;
}

function showPinError() {
    document.getElementById('pinError').classList.remove('hidden');
    document.getElementById('pinInput').value = '';
    document.getElementById('pinInput').focus();
}
