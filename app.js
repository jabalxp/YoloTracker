/* ==========================================================================
   YOLO Tracker Pro - Lógica da Aplicação (Frontend)
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    // Inicializa Lucide Icons
    lucide.createIcons();

    // Elementos do DOM
    const inputTargetEpochs = document.getElementById('input-target-epochs');
    const toggleRefresh = document.getElementById('toggle-refresh');
    const refreshProgressCircle = document.getElementById('refresh-progress');
    const btnManualRefresh = document.getElementById('btn-manual-refresh');
    const statusBadge = document.getElementById('status-badge');
    const errorBanner = document.getElementById('error-banner');
    const errorMessage = document.getElementById('error-message');
    const searchTableInput = document.getElementById('search-table');
    const toastContainer = document.getElementById('toast-container');

    // Estado da Aplicação
    let targetEpochs = parseInt(inputTargetEpochs.value) || 30;
    let isAutoRefreshActive = toggleRefresh.checked;
    let refreshSecondsLeft = 10;
    const REFRESH_INTERVAL_SECONDS = 10;
    let refreshTimerInterval = null;
    let countdownTimerInterval = null;
    let lastFetchedEpochs = 0;
    let rawData = null;

    // Instâncias do Chart.js
    let chartLosses = null;
    let chartMetrics = null;
    let chartLR = null;
    let currentLossType = 'box'; // 'box', 'cls' ou 'dfl'

    // Configuração Padrão do Chart.js para Tema Dark
    Chart.defaults.color = '#94a3b8'; // --text-secondary
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.95)';
    Chart.defaults.plugins.tooltip.titleColor = '#f8fafc';
    Chart.defaults.plugins.tooltip.bodyColor = '#cbd5e1';
    Chart.defaults.plugins.tooltip.borderColor = 'rgba(255, 255, 255, 0.08)';
    Chart.defaults.plugins.tooltip.borderWidth = 1;
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;

    // ==========================================================================
    // Funções Utilitárias de Formatação
    // ==========================================================================

    function formatDuration(seconds) {
        if (isNaN(seconds) || seconds < 0) return '--';
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.round(seconds % 60);

        let parts = [];
        if (h > 0) parts.push(`${h}h`);
        if (m > 0 || h > 0) parts.push(`${m}m`);
        parts.push(`${s}s`);
        return parts.join(' ');
    }

    function formatNumber(num, decimals = 5) {
        if (num === null || num === undefined || isNaN(num)) return '--';
        return parseFloat(num).toFixed(decimals);
    }

    // ==========================================================================
    // Notificações Toast e Feedbacks Visuais
    // ==========================================================================

    function showToast(title, desc, icon = 'info') {
        const toast = document.createElement('div');
        toast.className = 'toast';
        
        let iconName = 'bell';
        if (icon === 'success') iconName = 'check-circle';
        if (icon === 'warning') iconName = 'alert-circle';
        if (icon === 'trophy') iconName = 'trophy';

        toast.innerHTML = `
            <div class="toast-icon"><i data-lucide="${iconName}"></i></div>
            <div class="toast-content">
                <span class="toast-title">${title}</span>
                <span class="toast-desc">${desc}</span>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        lucide.createIcons();

        // Animação de saída e remoção
        setTimeout(() => {
            toast.classList.add('toast-hide');
            setTimeout(() => toast.remove(), 400);
        }, 5000);
    }

    function applyUpdatePulse() {
        const cards = document.querySelectorAll('.kpi-card');
        cards.forEach(card => {
            card.classList.add('pulse-update');
            setTimeout(() => card.classList.remove('pulse-update'), 600);
        });
    }

    // ==========================================================================
    // Gestão de Gráficos (Chart.js)
    // ==========================================================================

    function initCharts(data) {
        const epochs = data.charts.epochs;

        // --- Gráfico 1: Perdas ---
        const ctxLoss = document.getElementById('chart-losses').getContext('2d');
        
        const trainGlow = ctxLoss.createLinearGradient(0, 0, 0, 300);
        trainGlow.addColorStop(0, 'rgba(99, 102, 241, 0.15)');
        trainGlow.addColorStop(1, 'rgba(99, 102, 241, 0.0)');
        
        const valGlow = ctxLoss.createLinearGradient(0, 0, 0, 300);
        valGlow.addColorStop(0, 'rgba(6, 182, 212, 0.15)');
        valGlow.addColorStop(1, 'rgba(6, 182, 212, 0.0)');

        chartLosses = new Chart(ctxLoss, {
            type: 'line',
            data: {
                labels: epochs,
                datasets: [
                    {
                        label: 'Treinamento (Train)',
                        data: data.charts.losses.train_box,
                        borderColor: '#6366f1',
                        backgroundColor: trainGlow,
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#6366f1'
                    },
                    {
                        label: 'Validação (Val)',
                        data: data.charts.losses.val_box,
                        borderColor: '#06b6d4',
                        backgroundColor: valGlow,
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#06b6d4'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        title: { display: true, text: 'Época', color: '#64748b' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        title: { display: true, text: 'Valor da Perda', color: '#64748b' }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#f8fafc',
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });

        // --- Gráfico 2: Evolução de Métricas ---
        const ctxMetrics = document.getElementById('chart-metrics-evolution').getContext('2d');
        chartMetrics = new Chart(ctxMetrics, {
            type: 'line',
            data: {
                labels: epochs,
                datasets: [
                    {
                        label: 'mAP50',
                        data: data.charts.metrics.mAP50,
                        borderColor: '#f59e0b',
                        borderWidth: 2.5,
                        tension: 0.3,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#f59e0b'
                    },
                    {
                        label: 'mAP50-95',
                        data: data.charts.metrics.mAP50_95,
                        borderColor: '#6366f1',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#6366f1'
                    },
                    {
                        label: 'Precisão',
                        data: data.charts.metrics.precision,
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                        borderDash: [4, 4],
                        tension: 0.3,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    },
                    {
                        label: 'Recall',
                        data: data.charts.metrics.recall,
                        borderColor: '#ec4899',
                        borderWidth: 1.5,
                        borderDash: [4, 4],
                        tension: 0.3,
                        pointRadius: 0,
                        pointHoverRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        title: { display: true, text: 'Época', color: '#64748b' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        min: 0,
                        max: 1.0,
                        title: { display: true, text: 'Pontuação (0 - 1)', color: '#64748b' }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#f8fafc',
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });

        // --- Gráfico 3: Taxa de Aprendizado (Learning Rate) ---
        const ctxLR = document.getElementById('chart-lr').getContext('2d');
        const lrGlow = ctxLR.createLinearGradient(0, 0, 0, 200);
        lrGlow.addColorStop(0, 'rgba(6, 182, 212, 0.1)');
        lrGlow.addColorStop(1, 'rgba(6, 182, 212, 0.0)');

        chartLR = new Chart(ctxLR, {
            type: 'line',
            data: {
                labels: epochs,
                datasets: [{
                    label: 'Learning Rate (pg0)',
                    data: data.charts.lr,
                    borderColor: '#06b6d4',
                    backgroundColor: lrGlow,
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.03)' },
                        title: { display: true, text: 'Época', color: '#64748b' }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.03)' },
                        title: { display: true, text: 'Taxa de Otimização', color: '#64748b' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    function updateCharts(data) {
        if (!chartLosses) {
            initCharts(data);
            return;
        }

        const epochs = data.charts.epochs;

        // Atualizar Dados das Perdas
        chartLosses.data.labels = epochs;
        chartLosses.data.datasets[0].data = data.charts.losses[`train_${currentLossType}`];
        chartLosses.data.datasets[1].data = data.charts.losses[`val_${currentLossType}`];
        chartLosses.update('none');

        // Atualizar Dados das Métricas
        chartMetrics.data.labels = epochs;
        chartMetrics.data.datasets[0].data = data.charts.metrics.mAP50;
        chartMetrics.data.datasets[1].data = data.charts.metrics.mAP50_95;
        chartMetrics.data.datasets[2].data = data.charts.metrics.precision;
        chartMetrics.data.datasets[3].data = data.charts.metrics.recall;
        chartMetrics.update('none');

        // Atualizar Dados da Taxa de Aprendizado
        chartLR.data.labels = epochs;
        chartLR.data.datasets[0].data = data.charts.lr;
        chartLR.update('none');
    }

    // Gerencia o seletor do tipo de perdas
    document.querySelectorAll('[data-chart="loss"]').forEach(button => {
        button.addEventListener('click', (e) => {
            document.querySelectorAll('[data-chart="loss"]').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            
            currentLossType = e.target.getAttribute('data-type');
            
            if (rawData && chartLosses) {
                const labelsMap = { 'box': 'Box Loss', 'cls': 'Class Loss', 'dfl': 'DFL Loss' };
                const labelText = labelsMap[currentLossType];
                
                chartLosses.data.datasets[0].label = `Treinamento (${labelText})`;
                chartLosses.data.datasets[1].label = `Validação (${labelText})`;
                chartLosses.data.datasets[0].data = rawData.charts.losses[`train_${currentLossType}`];
                chartLosses.data.datasets[1].data = rawData.charts.losses[`val_${currentLossType}`];
                chartLosses.update();
            }
        });
    });

    // ==========================================================================
    // Renderização e Cálculos de Dados na UI
    // ==========================================================================

    function updateKPIs(data) {
        const lastEpoch = data.last_epoch;
        const bestEpoch = data.best_epoch;
        const stats = data.stats;
        
        const currentEpoch = parseInt(lastEpoch.epoch) || 0;

        // 1. Card Progresso
        document.getElementById('val-current-epoch').textContent = currentEpoch;
        document.getElementById('val-target-epoch').textContent = targetEpochs;
        
        const progressPercent = Math.min(100, Math.round((currentEpoch / targetEpochs) * 100));
        document.getElementById('val-percent-badge').textContent = `${progressPercent}%`;
        document.getElementById('val-progress-bar').style.width = `${progressPercent}%`;

        const epochStatusText = document.getElementById('val-epoch-status');
        if (currentEpoch >= targetEpochs) {
            epochStatusText.innerHTML = '<span style="color: var(--accent-emerald)">Meta Concluída! 🎉</span>';
        } else {
            epochStatusText.textContent = `Faltam ${targetEpochs - currentEpoch} épocas para a meta.`;
        }

        // 2. Card Melhor Época (Mapeamento dos novos IDs nativos)
        if (bestEpoch) {
            document.getElementById('val-best-map50-main').textContent = formatNumber(bestEpoch['metrics/mAP50(B)'], 4);
            document.getElementById('val-best-epoch-badge').textContent = `Época ${bestEpoch.epoch}`;
            document.getElementById('val-best-map5095').textContent = formatNumber(bestEpoch['metrics/mAP50-95(B)'], 4);
            document.getElementById('val-best-precision').textContent = formatNumber(bestEpoch['metrics/precision(B)'], 4);
            document.getElementById('val-best-recall').textContent = formatNumber(bestEpoch['metrics/recall(B)'], 4);
        }

        // 3. Card Última Época (Mapeamento dos novos IDs nativos)
        if (lastEpoch) {
            document.getElementById('val-recent-map50-main').textContent = formatNumber(lastEpoch['metrics/mAP50(B)'], 4);
            document.getElementById('val-recent-epoch-badge').textContent = `Época ${lastEpoch.epoch}`;
            document.getElementById('val-recent-map5095').textContent = formatNumber(lastEpoch['metrics/mAP50-95(B)'], 4);
            document.getElementById('val-recent-precision').textContent = formatNumber(lastEpoch['metrics/precision(B)'], 4);
            document.getElementById('val-recent-recall').textContent = formatNumber(lastEpoch['metrics/recall(B)'], 4);
            document.getElementById('val-recent-footer').textContent = `Última época atualizada: Época ${lastEpoch.epoch}`;
        }

        // 4. Card Cronometragem
        document.getElementById('val-time-elapsed').textContent = formatDuration(stats.total_time);
        document.getElementById('val-time-avg').textContent = `${Math.round(stats.avg_time_per_epoch)}s/época`;

        const timeRemainingElement = document.getElementById('val-time-remaining');
        const timeFooterElement = document.getElementById('val-time-footer');

        if (currentEpoch >= targetEpochs) {
            timeRemainingElement.textContent = 'Concluído';
            timeRemainingElement.style.color = 'var(--accent-emerald)';
            timeFooterElement.textContent = 'Treinamento completo na meta definida';
        } else {
            const epochsRemaining = targetEpochs - currentEpoch;
            const estimatedSecondsLeft = epochsRemaining * stats.avg_time_per_epoch;
            timeRemainingElement.textContent = formatDuration(estimatedSecondsLeft);
            timeRemainingElement.style.color = 'var(--accent-cyan)';
            
            const hoursRemaining = (estimatedSecondsLeft / 3600).toFixed(1);
            timeFooterElement.textContent = `Projeção: ~${hoursRemaining} horas para finalizar`;
        }

        // 5. Badge do Status no Header
        statusBadge.className = 'status-indicator';
        const statusTextSpan = statusBadge.querySelector('.status-text');
        
        if (stats.is_active) {
            statusBadge.classList.add('status-active');
            statusTextSpan.textContent = 'Treinando ⚡';
        } else if (currentEpoch >= targetEpochs) {
            statusBadge.classList.add('status-active');
            statusTextSpan.textContent = 'Finalizado ✅';
        } else {
            statusBadge.classList.add('status-paused');
            statusTextSpan.textContent = `Ocioso ⏸️ (Há ${formatDuration(stats.time_since_update)})`;
        }
    }

    function renderTable(history, bestEpoch) {
        const tbody = document.getElementById('table-body');
        tbody.innerHTML = '';

        if (history.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" class="table-loading">Nenhum dado de histórico disponível.</td></tr>`;
            return;
        }

        history.forEach(row => {
            const tr = document.createElement('tr');
            
            const isBest = bestEpoch && parseInt(row.epoch) === parseInt(bestEpoch.epoch);
            if (isBest) {
                tr.className = 'best-row';
            }

            // Define cor dinâmica sutil baseada no mAP50 principal
            const map50 = row['metrics/mAP50(B)'];
            let map50Color = 'var(--text-primary)';
            if (map50 > 0.85) map50Color = 'var(--accent-emerald)';
            else if (map50 > 0.60) map50Color = 'var(--accent-gold)';
            else if (map50 < 0.40) map50Color = 'var(--accent-coral)';

            tr.innerHTML = `
                <td>${row.epoch}</td>
                <td>${formatDuration(row.time)}</td>
                <td style="font-weight: 700; color: ${map50Color}">${formatNumber(map50, 5)}</td>
                <td>${formatNumber(row['metrics/mAP50-95(B)'], 5)}</td>
                <td>${formatNumber(row['metrics/precision(B)'], 5)}</td>
                <td>${formatNumber(row['metrics/recall(B)'], 5)}</td>
                <td>
                    <span class="table-badge table-badge-loss" title="Treino">${formatNumber(row['train/box_loss'], 4)}</span>
                    <span class="table-badge table-badge-loss" title="Val" style="color: var(--accent-cyan)">${formatNumber(row['val/box_loss'], 4)}</span>
                </td>
                <td>
                    <span class="table-badge table-badge-loss" title="Treino">${formatNumber(row['train/cls_loss'], 4)}</span>
                    <span class="table-badge table-badge-loss" title="Val" style="color: var(--accent-cyan)">${formatNumber(row['val/cls_loss'], 4)}</span>
                </td>
                <td>
                    <span class="table-badge table-badge-loss" title="Treino">${formatNumber(row['train/dfl_loss'], 4)}</span>
                    <span class="table-badge table-badge-loss" title="Val" style="color: var(--accent-cyan)">${formatNumber(row['val/dfl_loss'], 4)}</span>
                </td>
                <td style="font-family: monospace; font-size: 11px; color: var(--accent-cyan)">${parseFloat(row['lr/pg0']).toExponential(4)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    // ==========================================================================
    // Mecanismos de Busca / Filtro e Inputs
    // ==========================================================================

    searchTableInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#table-body tr');

        rows.forEach(row => {
            const epochCell = row.querySelector('td:first-child');
            if (!epochCell) return;
            
            const epochText = epochCell.textContent.toLowerCase();
            
            if (epochText.includes(searchTerm) || searchTerm === '') {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });
    });

    inputTargetEpochs.addEventListener('change', (e) => {
        let val = parseInt(e.target.value);
        if (isNaN(val) || val < 1) val = 30;
        targetEpochs = val;
        inputTargetEpochs.value = val;
        
        if (rawData) {
            updateKPIs(rawData);
        }
    });

    // ==========================================================================
    // Requisição HTTP de Dados (Fetch API)
    // ==========================================================================

    async function fetchTrainingData(isInitial = false) {
        btnManualRefresh.classList.add('btn-refreshing');
        btnManualRefresh.disabled = true;

        try {
            const response = await fetch('data.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const json = await response.json();
            
            if (!json.success) {
                throw new Error(json.message || 'Erro deconhecido ao buscar dados.');
            }

            errorBanner.classList.add('hidden');
            rawData = json;

            // Renderizar na Interface
            updateKPIs(json);
            updateCharts(json);
            renderTable(json.history, json.best_epoch);
            runAIDiagnostics(json);

            // Verificar novas épocas para disparar Notificação
            const currentTotalEpochs = json.stats.total_epochs;
            if (!isInitial && currentTotalEpochs > lastFetchedEpochs) {
                const newEpochs = currentTotalEpochs - lastFetchedEpochs;
                
                applyUpdatePulse();
                
                showToast(
                    `Treinamento Atualizado! 🚀`, 
                    `Concluída(s) +${newEpochs} época(s). Total: ${currentTotalEpochs}. mAP50: ${formatNumber(json.last_epoch['metrics/mAP50(B)'], 4)}`,
                    'success'
                );

                if (newEpochs > 0 && parseInt(json.best_epoch.epoch) === parseInt(json.last_epoch.epoch)) {
                    showToast(
                        `Nova Melhor Época! 🏆`,
                        `Época ${json.best_epoch.epoch} atingiu o melhor mAP50 do treinamento: ${formatNumber(json.best_epoch['metrics/mAP50(B)'], 4)}!`,
                        'trophy'
                    );
                }
            }

            lastFetchedEpochs = currentTotalEpochs;

        } catch (error) {
            console.error('Falha ao obter métricas:', error);
            errorBanner.classList.remove('hidden');
            errorMessage.textContent = `Não foi possível carregar as métricas do CSV: ${error.message}`;
            
            statusBadge.className = 'status-indicator status-inactive';
            statusBadge.querySelector('.status-text').textContent = 'Erro de Leitura';
            
            showToast('Erro de Conexão', 'Não foi possível ler os dados do CSV.', 'warning');
        } finally {
            btnManualRefresh.classList.remove('btn-refreshing');
            btnManualRefresh.disabled = false;
            refreshSecondsLeft = REFRESH_INTERVAL_SECONDS;
        }
    }

    // ==========================================================================
    // Controle do Timer de Auto-Refresh
    // ==========================================================================

    function setRefreshRingProgress(percent) {
        const radius = refreshProgressCircle.r.baseVal.value;
        const circumference = 2 * Math.PI * radius;
        const offset = circumference - (percent / 100) * circumference;
        refreshProgressCircle.style.strokeDashoffset = offset;
    }

    function startAutoRefreshTimer() {
        stopAutoRefreshTimer();

        refreshSecondsLeft = REFRESH_INTERVAL_SECONDS;
        setRefreshRingProgress(100);

        refreshTimerInterval = setInterval(() => {
            if (isAutoRefreshActive) {
                fetchTrainingData();
            }
        }, REFRESH_INTERVAL_SECONDS * 1000);

        countdownTimerInterval = setInterval(() => {
            if (isAutoRefreshActive) {
                refreshSecondsLeft -= 1;
                if (refreshSecondsLeft < 0) {
                    refreshSecondsLeft = REFRESH_INTERVAL_SECONDS;
                }
                const percent = (refreshSecondsLeft / REFRESH_INTERVAL_SECONDS) * 100;
                setRefreshRingProgress(percent);
            }
        }, 1000);
    }

    function stopAutoRefreshTimer() {
        if (refreshTimerInterval) clearInterval(refreshTimerInterval);
        if (countdownTimerInterval) clearInterval(countdownTimerInterval);
    }

    toggleRefresh.addEventListener('change', (e) => {
        isAutoRefreshActive = e.target.checked;
        if (isAutoRefreshActive) {
            startAutoRefreshTimer();
            refreshProgressCircle.parentElement.style.opacity = '1';
        } else {
            stopAutoRefreshTimer();
            setRefreshRingProgress(0);
            refreshProgressCircle.parentElement.style.opacity = '0.3';
        }
    });

    btnManualRefresh.addEventListener('click', () => {
        fetchTrainingData();
    });

    // ==========================================================================
    // Motor Analítico do AI Copilot (Diagnóstico de Curvas de Deep Learning)
    // ==========================================================================

    function runAIDiagnostics(data) {
        const history = data.history || [];
        // O histórico vem em ordem reversa no JSON (última época primeiro)
        // Vamos inverter a ordem para criar uma cópia ordenada cronologicamente (da época 1 até a última)
        const chronologicalHistory = [...history].reverse();
        const totalEpochs = chronologicalHistory.length;

        const badge = document.getElementById('copilot-badge');
        const message = document.getElementById('copilot-message');
        const rightPanel = document.getElementById('copilot-right-panel');
        const recList = document.getElementById('copilot-rec-list');

        if (totalEpochs < 3) {
            badge.className = 'copilot-badge badge-good';
            badge.textContent = 'Inicializando';
            message.textContent = 'Coletando dados iniciais do YOLO. Aguarde pelo menos 3 épocas concluídas para o primeiro diagnóstico estruturado.';
            recList.innerHTML = '<li>Continue acompanhando o warmup de treinamento.</li>';
            return;
        }

        const lastEntry = chronologicalHistory[totalEpochs - 1];
        const currentEpoch = lastEntry.epoch;
        
        let status = 'good'; // 'good', 'warning', 'danger'
        let statusLabel = 'Saudável ⚡';
        let mainMsg = '';
        let recommendations = [];

        // 1. ANÁLISE DE OVERFITTING (Decorar Dados de Treino)
        // Critério: Perda de treino caindo constantemente, mas perda de validação subindo nas últimas épocas.
        let valLossTrendRising = false;
        let trainLossTrendFalling = false;
        
        if (totalEpochs >= 8) {
            const lastN = 6; // Analisar as últimas 6 épocas
            const slice = chronologicalHistory.slice(-lastN);
            
            // Calcula diferenças nas perdas de validação (Box Loss)
            let valUpCount = 0;
            let trainDownCount = 0;
            for (let i = 1; i < slice.length; i++) {
                if (slice[i]['val/box_loss'] > slice[i-1]['val/box_loss']) valUpCount++;
                if (slice[i]['train/box_loss'] < slice[i-1]['train/box_loss']) trainDownCount++;
            }
            
            // Se a perda de validação subiu na maior parte das últimas épocas e a de treino caiu
            if (valUpCount >= 4 && trainDownCount >= 4) {
                valLossTrendRising = true;
                trainLossTrendFalling = true;
            }
        }

        // 2. ANÁLISE DE ESTAGNAÇÃO DO APRENDIZADO (Convergência Estagnada)
        let isStagnated = false;
        if (totalEpochs >= 15) {
            const lastN = 10;
            const slice = chronologicalHistory.slice(-lastN);
            const mAPs = slice.map(row => row['metrics/mAP50(B)']);
            const maxMAP = Math.max(...mAPs);
            const minMAP = Math.min(...mAPs);
            
            // Se a flutuação do mAP50 nas últimas 10 épocas for extremamente insignificante
            if ((maxMAP - minMAP) < 0.0015 && lastEntry['lr/pg0'] < 1e-4) {
                isStagnated = true;
            }
        }

        // 3. ANÁLISE DE CONGRUÊNCIA DE CLASSES (Dificuldade de Classificação)
        let isClsLossHigh = false;
        if (lastEntry['train/cls_loss'] > lastEntry['train/box_loss'] * 2.5) {
            isClsLossHigh = true;
        }

        // 4. AVALIAÇÃO DE RESULTADOS EXCELENTES
        const map50 = lastEntry['metrics/mAP50(B)'];
        const map5095 = lastEntry['metrics/mAP50-95(B)'];

        // Determinação do Diagnóstico e Recomendações
        if (valLossTrendRising && trainLossTrendFalling) {
            status = 'danger';
            statusLabel = 'Overfitting ⚠️';
            mainMsg = `Alerta crítico na Época ${currentEpoch}: Identificamos um padrão clássico de <strong>Overfitting</strong>. Enquanto a perda de treino continua diminuindo, o erro nas imagens inéditas de validação está aumentando de forma consistente nas últimas épocas.`;
            recommendations = [
                'Aplique <strong>Early Stopping</strong> (interrompa o treino) e baixe a Melhor Época salva.',
                'Aumente as técnicas de <strong>Data Augmentation</strong> no Colab para diversificar o dataset.',
                'Adicione mais imagens de cenários reais ou aumente a taxa de <strong>Dropout</strong> no modelo.',
                'Reduza a complexidade do modelo (ex: se está usando YOLO11m, tente YOLO11s).'
            ];
        } else if (isStagnated) {
            status = 'warning';
            statusLabel = 'Convergência ⏸️';
            mainMsg = `O modelo atingiu a **Estabilização de Aprendizado** na Época ${currentEpoch}. O ganho de precisão média (mAP50) variou menos que 0.15% nas últimas 10 épocas e a Taxa de Aprendizado (Learning Rate) já está extremamente baixa (${lastEntry['lr/pg0'].toExponential(2)}).`;
            recommendations = [
                'O modelo atingiu o ápice de aprendizado útil com os hiperparâmetros atuais.',
                'Considere <strong>encerrar o treinamento</strong> para economizar tempo de GPU do Colab.',
                'Se desejar maior precisão, tente reiniciar o treino com um <strong>dataset maior</strong> ou mude o otimizador.'
            ];
        } else if (isClsLossHigh) {
            status = 'warning';
            statusLabel = 'Dataset Ruidoso 🧐';
            mainMsg = `Detectamos desequilíbrio na Época ${currentEpoch}: A perda de classificação (Class Loss: ${lastEntry['train/cls_loss'].toFixed(4)}) está anormalmente alta comparada à perda de caixa (Box Loss: ${lastEntry['train/box_loss'].toFixed(4)}). A rede neural localiza os objetos facilmente, mas falha em nomeá-los.`;
            recommendations = [
                'Verifique se existem classes muito parecidas visualmente que confundem a rede neural.',
                'Revise as anotações do seu dataset; rótulos errados ou trocados causam este sintoma exato.',
                'Adicione mais amostras para as classes que estão apresentando menor precisão individual.'
            ];
        } else if (map50 > 0.85) {
            status = 'good';
            statusLabel = 'Excelente ✨';
            mainMsg = `Parabéns! O treinamento na Época ${currentEpoch} apresenta métricas excepcionais. O mAP50 alcançou a incrível marca de <strong>${(map50 * 100).toFixed(1)}%</strong> com enquadramento de caixa estável (mAP50-95: ${(map5095 * 100).toFixed(1)}%).`;
            recommendations = [
                'Modelo perfeitamente maduro e pronto para ser testado em ambientes de homologação.',
                'Examine se a taxa de perdas de validação continua caindo lentamente; se sim, o modelo ainda pode melhorar.',
                'Gere o arquivo de pesos <strong>.pt</strong> ou converta para <strong>ONNX</strong> para implantação.'
            ];
        } else {
            status = 'good';
            statusLabel = 'Saudável ⚡';
            mainMsg = `Treinamento saudável e ativo na Época ${currentEpoch}. As curvas de perdas de treinamento e validação continuam caindo de forma harmoniosa, indicando que o modelo está absorvendo conhecimento de forma estável.`;
            recommendations = [
                'Nenhuma anomalia de rede detectada. O modelo está aprendendo de forma sólida.',
                'Mantenha o treinamento ativo; a meta de épocas está sendo buscada com estabilidade.',
                'Acompanhe o mAP50 no gráfico para observar a estabilização dos acertos.'
            ];
        }

        // Atualiza Badge
        badge.className = `copilot-badge badge-${status}`;
        badge.textContent = statusLabel;

        // Atualiza Mensagem Principal
        message.innerHTML = mainMsg;

        // Atualiza Painel de Recomendações e Estilos
        rightPanel.className = `copilot-right status-${status}`;
        recList.className = `copilot-rec-list status-${status}`;
        
        recList.innerHTML = recommendations.map(rec => `<li>${rec}</li>`).join('');
    }

    // ==========================================================================
    // Controle do Modo TV (Kiosk Mode)
    // ==========================================================================



    // ==========================================================================
    // Inicialização da Aplicação
    // ==========================================================================
    
    fetchTrainingData(true);
    
    if (isAutoRefreshActive) {
        startAutoRefreshTimer();
    }
});
