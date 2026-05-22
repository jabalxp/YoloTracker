<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YOLO Tracker Pro - Monitoramento de Treinamento</title>
    
    <!-- Google Fonts: Outfit (títulos) e Inter (textos) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons para ícones modernos -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Chart.js para gráficos interativos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Folha de Estilo Personalizada -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Fundo Animado Decorativo -->
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>

    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="header-main">
                <div class="logo-area">
                    <div class="logo-icon-container">
                        <i data-lucide="cpu" class="logo-icon"></i>
                    </div>
                    <div>
                        <h1>YOLO Tracker Pro</h1>
                        <p class="subtitle">Monitoramento de Treinamento em Tempo Real</p>
                    </div>
                </div>
                
                <div class="status-indicator" id="status-badge">
                    <span class="status-dot"></span>
                    <span class="status-text">Verificando...</span>
                </div>
            </div>

            <div class="header-controls">
                <!-- Link para o Guia -->
                <a href="guia.php" class="btn" style="background: rgba(255,255,255,0.03); border-color: rgba(255,255,255,0.08);" target="_blank" title="Entenda os Gráficos e Métricas">
                    <i data-lucide="help-circle" style="color: var(--accent-cyan)"></i>
                    <span>Guia de Gráficos</span>
                </a>


                <!-- Meta de Épocas -->
                <div class="control-group">
                    <label for="input-target-epochs">
                        <i data-lucide="target" class="control-icon"></i> Meta Épocas
                    </label>
                    <input type="number" id="input-target-epochs" value="30" min="1" max="1000">
                </div>

                <!-- Auto Refresh -->
                <div class="refresh-control">
                    <div class="toggle-container">
                        <input type="checkbox" id="toggle-refresh" checked>
                        <label for="toggle-refresh" class="toggle-slider">
                            <span class="toggle-knob"></span>
                        </label>
                    </div>
                    <span class="refresh-label">Auto-Update</span>
                    <div class="progress-ring-container">
                        <svg class="progress-ring" width="24" height="24">
                            <circle class="progress-ring-bg" stroke="rgba(255,255,255,0.1)" stroke-width="2" fill="transparent" r="10" cx="12" cy="12"/>
                            <circle id="refresh-progress" class="progress-ring-circle" stroke="var(--accent-cyan)" stroke-width="2" fill="transparent" r="10" cx="12" cy="12"/>
                        </svg>
                    </div>
                </div>

                <!-- Botão Forçar Atualização -->
                <button id="btn-manual-refresh" class="btn btn-primary" title="Atualizar agora">
                    <i data-lucide="refresh-cw" class="icon-refresh"></i>
                    <span>Atualizar</span>
                </button>
            </div>
        </header>

        <!-- Seção de Alertas e Erros -->
        <div id="error-banner" class="banner banner-error hidden">
            <i data-lucide="alert-triangle"></i>
            <span id="error-message"></span>
        </div>

        <!-- Dashboard Grid -->
        <main class="dashboard-grid">
            
            <!-- Cards de Métricas Principais (KPIs) -->
            <section class="kpi-section">
                <!-- Card 1: Progresso -->
                <div class="kpi-card" id="card-progress">
                    <div class="kpi-header">
                        <span class="kpi-title">Progresso Geral</span>
                        <div class="kpi-icon-wrapper progress-icon"><i data-lucide="trending-up"></i></div>
                    </div>
                    <div class="kpi-value-container">
                        <div class="kpi-value"><span id="val-current-epoch">0</span><span class="kpi-divider">/</span><span id="val-target-epoch">100</span></div>
                        <div class="kpi-badge" id="val-percent-badge">0%</div>
                    </div>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" id="val-progress-bar" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="kpi-footer">
                        <span id="val-epoch-status">Carregando dados...</span>
                    </div>
                </div>

                <!-- Card 2: Melhor Época -->
                <div class="kpi-card" id="card-best">
                    <div class="kpi-header">
                        <span class="kpi-title">Melhor Época (mAP50)</span>
                        <div class="kpi-icon-wrapper best-icon"><i data-lucide="trophy"></i></div>
                    </div>
                    <div class="kpi-value-container">
                        <div class="kpi-value" id="val-best-map50-main">--</div>
                        <div class="kpi-badge gold" id="val-best-epoch-badge">Época --</div>
                    </div>
                    <div class="kpi-meta-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="meta-item">
                            <span class="meta-label">mAP50-95</span>
                            <span class="meta-val" id="val-best-map5095">--</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Precisão</span>
                            <span class="meta-val" id="val-best-precision">--</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Recall</span>
                            <span class="meta-val" id="val-best-recall">--</span>
                        </div>
                    </div>
                    <div class="kpi-footer">
                        <span>Época com maior assertividade de detecção</span>
                    </div>
                </div>

                <!-- Card 3: Últimas Métricas -->
                <div class="kpi-card" id="card-metrics">
                    <div class="kpi-header">
                        <span class="kpi-title">Último mAP50</span>
                        <div class="kpi-icon-wrapper recent-icon"><i data-lucide="activity"></i></div>
                    </div>
                    <div class="kpi-value-container">
                        <div class="kpi-value" id="val-recent-map50-main">--</div>
                        <div class="kpi-badge" id="val-recent-epoch-badge">Época --</div>
                    </div>
                    <div class="kpi-meta-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="meta-item">
                            <span class="meta-label">mAP50-95</span>
                            <span class="meta-val" id="val-recent-map5095">--</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Precisão</span>
                            <span class="meta-val" id="val-recent-precision">--</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Recall</span>
                            <span class="meta-val" id="val-recent-recall">--</span>
                        </div>
                    </div>
                    <div class="kpi-footer">
                        <span id="val-recent-footer">Aguardando atualização...</span>
                    </div>
                </div>

                <!-- Card 4: Cronometragem -->
                <div class="kpi-card" id="card-time">
                    <div class="kpi-header">
                        <span class="kpi-title">Cronometragem</span>
                        <div class="kpi-icon-wrapper time-icon"><i data-lucide="clock"></i></div>
                    </div>
                    <div class="kpi-value-container">
                        <div class="kpi-value" id="val-time-elapsed">--</div>
                        <div class="kpi-badge" id="val-time-avg">--/época</div>
                    </div>
                    <div class="kpi-meta-grid">
                        <div class="meta-item wide">
                            <span class="meta-label">Tempo Restante Est.</span>
                            <span class="meta-val highlight" id="val-time-remaining">--</span>
                        </div>
                    </div>
                    <div class="kpi-footer">
                        <span id="val-time-footer">Estimativa dinâmica de progresso</span>
                    </div>
                </div>
            </section>

            <!-- Painel AI Copilot (Diagnóstico de Curvas em Tempo Real) -->
            <section class="copilot-section">
                <div class="copilot-card">
                    <div class="copilot-container">
                        <!-- Lado Esquerdo: Status & Diagnóstico Geral -->
                        <div class="copilot-left">
                            <div class="copilot-brain-wrapper">
                                <i data-lucide="cpu" class="copilot-brain-icon"></i>
                            </div>
                            <div class="copilot-info">
                                <div class="copilot-title-row">
                                    <span class="copilot-label">AI Copilot</span>
                                    <span id="copilot-badge" class="copilot-badge badge-good">Analisando...</span>
                                </div>
                                <p id="copilot-message" class="copilot-message">
                                    Aguardando carregamento de métricas do YOLO para executar diagnóstico...
                                </p>
                            </div>
                        </div>

                        <!-- Lado Direito: Recomendações e Sugestões do Copilot -->
                        <div class="copilot-right status-good" id="copilot-right-panel">
                            <span class="copilot-rec-title">
                                <i data-lucide="sparkles" style="color: var(--accent-indigo)"></i> Recomendações de Deep Learning
                            </span>
                            <ul class="copilot-rec-list status-good" id="copilot-rec-list">
                                <li>Carregando recomendações do especialista...</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Seção de Gráficos -->
            <section class="charts-section">
                <!-- Gráfico 1: Perdas (Losses) -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title-area">
                            <h3>Curvas de Perdas (Losses)</h3>
                            <p>Comparativo entre Treinamento e Validação</p>
                        </div>
                        <div class="chart-legend-selectors">
                            <button class="selector-btn active" data-chart="loss" data-type="box">Box Loss</button>
                            <button class="selector-btn" data-chart="loss" data-type="cls">Class Loss</button>
                            <button class="selector-btn" data-chart="loss" data-type="dfl">DFL Loss</button>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="chart-losses"></canvas>
                    </div>
                </div>

                <!-- Gráfico 2: Métricas (mAP e Qualidade) -->
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="chart-title-area">
                            <h3>Métricas de Desempenho</h3>
                            <p>Evolução de mAP, Precisão e Recall</p>
                        </div>
                    </div>
                    <div class="chart-body">
                        <canvas id="chart-metrics-evolution"></canvas>
                    </div>
                </div>

                <!-- Gráfico 3: Taxa de Aprendizado (Learning Rate) -->
                <div class="chart-card col-span-2">
                    <div class="chart-header">
                        <div class="chart-title-area">
                            <h3>Taxa de Aprendizado (Learning Rate)</h3>
                            <p>Decaimento do hiperparâmetro de otimização</p>
                        </div>
                    </div>
                    <div class="chart-body lr-chart-body">
                        <canvas id="chart-lr"></canvas>
                    </div>
                </div>
            </section>

            <!-- Seção da Tabela de Épocas -->
            <section class="table-section">
                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title-area">
                            <h3>Histórico de Treinamento</h3>
                            <p>Lista completa e detalhada de todas as épocas concluídas</p>
                        </div>
                        <div class="table-controls">
                            <div class="search-box">
                                <i data-lucide="search" class="search-icon"></i>
                                <input type="text" id="search-table" placeholder="Buscar época...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Época</th>
                                    <th>Tempo Acumulado</th>
                                    <th>mAP50</th>
                                    <th>mAP50-95</th>
                                    <th>Precisão (B)</th>
                                    <th>Recall (B)</th>
                                    <th>Box Loss (Treino / Val)</th>
                                    <th>Cls Loss (Treino / Val)</th>
                                    <th>DFL Loss (Treino / Val)</th>
                                    <th>Learning Rate</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <tr>
                                    <td colspan="10" class="table-loading">Carregando dados históricos...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Container de Toasts para Notificações -->
    <div id="toast-container" class="toast-container"></div>


    <!-- Script de Aplicação Frontend -->
    <script src="app.js"></script>
</body>
</html>
