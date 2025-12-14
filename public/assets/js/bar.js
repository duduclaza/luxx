/**
 * TOTEM LUXX - Bar JS
 * Kanban com detalhes de pedido e chamada de cliente
 */

let pedidos = [];

// Dados demo - Bar vê bebidas + porções da cozinha
const pedidosDemo = [
    {
        id: 72, codigo: 'A-45', status: 'pendente', tempo: 0,
        itensCozinha: [
            { nome: 'Porção de Batata', qtd: 1, status: 'preparando' },
            { nome: 'Asa de Frango', qtd: 2, status: 'pendente' }
        ],
        itensBar: [
            { nome: 'Cerveja Heineken', qtd: 2 },
            { nome: 'Coca-Cola 600ml', qtd: 1 }
        ]
    },
    {
        id: 73, codigo: 'A-46', status: 'preparando', tempo: 120,
        itensCozinha: [],
        itensBar: [
            { nome: 'Caipirinha', qtd: 3 },
            { nome: 'Água Mineral', qtd: 1 }
        ]
    },
    {
        id: 70, codigo: 'A-43', status: 'pronto', tempo: 380,
        itensCozinha: [
            { nome: 'Pizza Marguerita', qtd: 1, status: 'pronto' }
        ],
        itensBar: [
            { nome: 'Cerveja Original', qtd: 4 }
        ]
    },
    {
        id: 68, codigo: 'A-41', status: 'chamando', tempo: 520,
        vezesChamado: 2,
        itensCozinha: [],
        itensBar: [
            { nome: 'Whisky', qtd: 1 },
            { nome: 'Água', qtd: 2 }
        ]
    }
];

document.addEventListener('DOMContentLoaded', () => {
    pedidos = pedidosDemo;
    renderCards();
    setInterval(updateTimes, 1000);
});

function renderCards() {
    const cols = {
        pendente: document.getElementById('columnNovos'),
        preparando: document.getElementById('columnPreparando'),
        pronto: document.getElementById('columnPronto'),
        chamando: document.getElementById('columnChamando')
    };

    Object.values(cols).forEach(c => c.innerHTML = '');
    const counts = { pendente: 0, preparando: 0, pronto: 0, chamando: 0 };

    pedidos.forEach(p => {
        counts[p.status]++;
        cols[p.status].appendChild(createCard(p));
    });

    document.getElementById('countNovos').textContent = counts.pendente;
    document.getElementById('countPreparando').textContent = counts.preparando;
    document.getElementById('countPronto').textContent = counts.pronto;
    document.getElementById('countChamando').textContent = counts.chamando;

    document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('pt-BR');
}

function createCard(p) {
    const div = document.createElement('div');
    let cardClass = 'order-card';
    if (p.status === 'pronto') cardClass += ' ready';
    if (p.status === 'chamando') cardClass += ' calling';
    div.className = cardClass;

    const mins = Math.floor(p.tempo / 60);
    const secs = p.tempo % 60;
    const time = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

    // Total de itens
    const totalItens = (p.itensCozinha?.length || 0) + (p.itensBar?.length || 0);

    // Botões baseado no status
    let buttons = '';
    if (p.status === 'pendente') {
        buttons = `<button class="card-btn primary" onclick="moveCard(${p.id}, 'preparando')">▶ Preparar</button>`;
    } else if (p.status === 'preparando') {
        buttons = `<button class="card-btn success" onclick="moveCard(${p.id}, 'pronto')">✓ Pronto</button>`;
    } else if (p.status === 'pronto') {
        buttons = `<button class="card-btn warning" onclick="chamarCliente(${p.id})">📢 Chamar Cliente</button>`;
    } else if (p.status === 'chamando') {
        buttons = `
            <button class="card-btn warning" onclick="chamarCliente(${p.id})" style="margin-bottom:8px">📢 Chamar Novamente</button>
            <button class="card-btn success" onclick="entregarPedido(${p.id})">✓ Entregue</button>
        `;
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
        <div style="margin-bottom:12px">
            <button onclick="openDetails(${p.id})" style="width:100%;padding:8px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#94a3b8;font-size:13px;cursor:pointer">
                👁️ Ver Detalhes (${totalItens} itens)
            </button>
        </div>
        ${p.vezesChamado ? `<div style="text-align:center;color:#f59e0b;font-size:12px;margin-bottom:8px">📢 Chamado ${p.vezesChamado}x</div>` : ''}
        ${buttons}
    `;

    return div;
}

function openDetails(id) {
    const p = pedidos.find(x => x.id === id);
    if (!p) return;

    let cozinhaHtml = '';
    if (p.itensCozinha && p.itensCozinha.length > 0) {
        cozinhaHtml = `
            <div style="margin-bottom:16px">
                <h4 style="font-size:14px;color:#f59e0b;margin-bottom:8px">🍳 Da Cozinha</h4>
                ${p.itensCozinha.map(i => `
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05)">
                        <span>${i.qtd}x ${i.nome}</span>
                        <span style="padding:2px 8px;border-radius:4px;font-size:12px;${i.status === 'pronto' ? 'background:rgba(34,197,94,0.2);color:#22c55e' : 'background:rgba(245,158,11,0.2);color:#f59e0b'}">${i.status === 'pronto' ? '✓ Pronto' : '⏳ Preparando'}</span>
                    </div>
                `).join('')}
            </div>
        `;
    }

    let barHtml = '';
    if (p.itensBar && p.itensBar.length > 0) {
        barHtml = `
            <div>
                <h4 style="font-size:14px;color:#3b82f6;margin-bottom:8px">🍺 Do Bar</h4>
                ${p.itensBar.map(i => `
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.05)">
                        <span>${i.qtd}x ${i.nome}</span>
                    </div>
                `).join('')}
            </div>
        `;
    }

    document.getElementById('detailsContent').innerHTML = `
        <div style="text-align:center;margin-bottom:16px">
            <span style="background:rgba(139,92,246,0.2);color:#a78bfa;padding:4px 12px;border-radius:8px">Cliente: ${p.codigo}</span>
            <h3 style="font-size:24px;font-weight:700;margin-top:8px">Pedido #${p.id}</h3>
        </div>
        ${cozinhaHtml}
        ${barHtml}
        <button onclick="closeDetails()" class="btn-secondary" style="width:100%;margin-top:16px">Fechar</button>
    `;

    document.getElementById('detailsModal').classList.remove('hidden');
}

function closeDetails() {
    document.getElementById('detailsModal').classList.add('hidden');
}

function moveCard(id, newStatus) {
    const p = pedidos.find(x => x.id === id);
    if (p) {
        p.status = newStatus;
        renderCards();
        // API: updateStatus(id, newStatus);
    }
}

function chamarCliente(id) {
    const p = pedidos.find(x => x.id === id);
    if (!p) return;

    p.status = 'chamando';
    p.vezesChamado = (p.vezesChamado || 0) + 1;

    // Tocar som
    const audio = document.getElementById('dingSound');
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(() => { });
    }

    renderCards();

    // Enviar para painel de chamada
    fetch('/api/chamadas', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'chamar',
            pedido_id: id,
            codigo_cliente: p.codigo,
            duracao: 30
        })
    }).catch(console.error);
}

function entregarPedido(id) {
    pedidos = pedidos.filter(x => x.id !== id);
    renderCards();

    fetch('/api/pedidos', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'entregar', pedido_id: id })
    }).catch(console.error);
}

function updateTimes() {
    pedidos.forEach(p => {
        if (p.status !== 'pronto' && p.status !== 'chamando') p.tempo++;
    });
    renderCards();
}

// Modal exit (mesmo da cozinha)
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
            if (data.success) window.location.href = '/modulos';
            else showPinError();
        })
        .catch(() => {
            if (pin === '1234') window.location.href = '/modulos';
            else showPinError();
        });

    return false;
}

function showPinError() {
    document.getElementById('pinError').classList.remove('hidden');
    document.getElementById('pinInput').value = '';
    document.getElementById('pinInput').focus();
}
