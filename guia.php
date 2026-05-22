<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YOLO Tracker Pro - Guia de Aprendizado do Dashboard</title>
    
    <!-- Google Fonts: Outfit e Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* Estilos adicionais exclusivos para a página do guia */
        .guide-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 24px;
        }
        
        .guide-card {
            background: var(--bg-dark-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .guide-header-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .guide-title {
            font-family: var(--font-title);
            font-size: 26px;
            font-weight: 700;
            background: linear-gradient(to right, #ffffff, #d1d5db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topic-section {
            margin-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 24px;
        }

        .topic-title {
            font-family: var(--font-title);
            font-size: 20px;
            font-weight: 600;
            color: var(--accent-cyan);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .topic-title.purple { color: var(--accent-indigo); }
        .topic-title.emerald { color: var(--accent-emerald); }
        .topic-title.gold { color: var(--accent-gold); }
        .topic-title.coral { color: var(--accent-coral); }

        .guide-text {
            color: var(--text-secondary);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .guide-text strong {
            color: var(--text-primary);
        }

        /* Box de Destaque Didático (Metáforas/Explicação simples) */
        .didactic-box {
            background: rgba(6, 182, 212, 0.04);
            border-left: 4px solid var(--accent-cyan);
            padding: 16px 20px;
            border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
            margin: 18px 0;
        }

        .didactic-box.purple {
            background: rgba(99, 102, 241, 0.04);
            border-left-color: var(--accent-indigo);
        }

        .didactic-box.emerald {
            background: rgba(16, 185, 129, 0.04);
            border-left-color: var(--accent-emerald);
        }

        .didactic-title {
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-primary);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .didactic-desc {
            font-size: 13.5px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Regras de Direção do Gráfico (Subir/Descer) */
        .indicator-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            margin: 8px 0 16px 0;
        }

        .indicator-up {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--accent-emerald);
        }

        .indicator-down {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--accent-coral);
        }
        
        .indicator-neutral {
            background: rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.2);
            color: var(--accent-cyan);
        }

        /* Lista de Itens do Guia */
        .guide-list {
            list-style: none;
            padding-left: 4px;
            margin: 12px 0;
        }

        .guide-list li {
            position: relative;
            padding-left: 20px;
            margin-bottom: 10px;
            font-size: 14.5px;
            color: var(--text-secondary);
        }

        .guide-list li::before {
            content: '•';
            position: absolute;
            left: 6px;
            color: var(--accent-cyan);
            font-weight: bold;
            font-size: 16px;
        }
        
        .guide-list.purple li::before { color: var(--accent-indigo); }
    </style>
</head>
<body>
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>

    <div class="guide-container">
        <!-- Header -->
        <header class="app-header">
            <div class="logo-area">
                <div class="logo-icon-container">
                    <i data-lucide="book-open" class="logo-icon"></i>
                </div>
                <div>
                    <h1>Guia do Dashboard</h1>
                    <p class="subtitle">Entenda tudo sobre o treinamento do seu modelo YOLO</p>
                </div>
            </div>
            
            <a href="index.php" class="btn btn-primary">
                <i data-lucide="arrow-left"></i>
                <span>Voltar ao Dashboard</span>
            </a>
        </header>

        <!-- Card 1: Curvas de Perda -->
        <div class="guide-card">
            <h2 class="guide-title">
                <i data-lucide="trending-down" style="color: var(--accent-coral)"></i>
                <span>1. Curvas de Perdas (Losses)</span>
            </h2>
            
            <div class="indicator-badge indicator-down">
                <i data-lucide="arrow-down-circle"></i> O GRÁFICO DEVE DESCER (QUANTO MENOR, MELHOR!)
            </div>

            <p class="guide-text">
                As **Curvas de Perda** (ou *Loss Curves*) são o principal indicador de que o seu modelo está de fato estudando e se aprimorando. A "perda" é uma nota matemática para o **tamanho do erro** que o modelo cometeu ao tentar prever as coisas.
            </p>

            <div class="didactic-box">
                <div class="didactic-title">
                    <i data-lucide="lightbulb"></i> Analogia Simples
                </div>
                <div class="didactic-desc">
                    Pense na perda como a quantidade de erros em uma prova de 100 questões. No primeiro dia de aula, você chuta quase tudo e erra 90 questões (Perda = 90). Conforme você estuda dia após dia (época após época), você passa a errar 50, depois 20, depois apenas 3 questões. **O erro diminuir significa que você está aprendendo!**
                </div>
            </div>

            <div class="topic-section">
                <h3 class="topic-title coral">
                    <i data-lucide="box"></i> Box Loss (Perda de Caixa)
                </h3>
                <p class="guide-text">
                    Mede o quão bom o modelo é em **desenhar o retângulo perfeito** ao redor do objeto na imagem. 
                    Se a caixa que o modelo desenhou ficou muito grande, muito pequena ou deslocada para o lado, a Box Loss aumenta. Conforme ela desce, significa que o enquadramento do objeto está ficando cirúrgico.
                </p>
            </div>

            <div class="topic-section">
                <h3 class="topic-title coral">
                    <i data-lucide="tag"></i> Class Loss (Perda de Classificação)
                </h3>
                <p class="guide-text">
                    Mede o quão bom o modelo é em **dar o nome correto** ao objeto detectado. 
                    Por exemplo, se o modelo enquadrou perfeitamente uma pessoa na rua, mas escreveu na legenda que é um "carro", ele comete um erro gravíssimo de classificação, fazendo essa perda subir. Conforme ela desce, o modelo confunde menos as categorias.
                </p>
            </div>

            <div class="topic-section">
                <h3 class="topic-title coral">
                    <i data-lucide="sparkles"></i> DFL Loss (Distribution Focal Loss)
                </h3>
                <p class="guide-text">
                    É uma perda de ajuste fino para as bordas das caixas. Ela ajuda a rede a lidar com objetos que têm limites difíceis de enxergar ou que estão parcialmente escondidos/desfocados. A queda dessa métrica indica que o modelo está aprendendo a detectar detalhes muito finos de enquadramento.
                </p>
            </div>

            <div class="topic-section">
                <h3 class="topic-title purple">
                    <i data-lucide="git-compare"></i> Treinamento (Train) vs Validação (Val)
                </h3>
                <p class="guide-text">
                    No gráfico de perdas, você verá duas linhas: a de **Treinamento** (Train) e a de **Validação** (Val).
                </p>
                <ul class="guide-list purple">
                    <li><strong>Linha de Treinamento (Train - Roxo)</strong>: É o modelo fazendo exercícios de matemática *com consulta* ao gabarito. Ele erra, vê onde errou, e ajusta seus neurônios na hora. Por isso, essa linha quase sempre desce perfeitamente.</li>
                    <li><strong>Linha de Validação (Val - Ciano)</strong>: É uma *prova surpresa* com fotos inéditas que o modelo nunca viu antes. Ele tenta desenhar as caixas e dar os nomes sem consulta. É essa linha que diz se o modelo ficou inteligente de verdade para a vida real.</li>
                </ul>
                <div class="didactic-box purple">
                    <div class="didactic-title"><i data-lucide="shield-alert"></i> Cuidado com o "Overfitting" (Decorar a Prova)</div>
                    <div class="didactic-desc">
                        Se a linha de **Treinamento** continuar descendo rumo a zero, mas a linha de **Validação** parar de descer e começar a **SUBIR**, o modelo entrou em *Overfitting*. Isso significa que ele decorou as imagens de treino de forma tão perfeita que perdeu a capacidade de raciocinar com imagens novas. A melhor época de treino é sempre onde a perda de Validação atinge o menor ponto estável antes de começar a subir!
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Métricas de Desempenho -->
        <div class="guide-card">
            <h2 class="guide-title">
                <i data-lucide="trending-up" style="color: var(--accent-emerald)"></i>
                <span>2. Métricas de Desempenho (Desempenho Geral)</span>
            </h2>
            
            <div class="indicator-badge indicator-up">
                <i data-lucide="arrow-up-circle"></i> O GRÁFICO DEVE SUBIR (QUANTO MAIOR, MELHOR!)
            </div>

            <p class="guide-text">
                Enquanto as perdas medem o "erro", as métricas de desempenho medem os **acertos do modelo** em uma escala padrão de **0 a 1** (onde 1 representa 100% de perfeição). Aqui, subir é excelente!
            </p>

            <div class="topic-section">
                <h3 class="topic-title emerald">
                    <i data-lucide="check"></i> Precisão (Precision)
                </h3>
                <p class="guide-text">
                    Responde à pergunta: **"De tudo que o modelo apontou na tela, quanto ele realmente acertou?"**
                    Se o modelo desenhar 10 caixas na imagem dizendo que são "pessoas", e na verdade 9 eram pessoas e 1 era um poste de luz, a Precisão dele é de **0.90 (90%)**. 
                    Uma Precisão alta evita "alarmes falsos".
                </p>
            </div>

            <div class="topic-section">
                <h3 class="topic-title emerald">
                    <i data-lucide="eye"></i> Recall (Revocação)
                </h3>
                <p class="guide-text">
                    Responde à pergunta: **"De todos os objetos que realmente estavam na imagem, quantos o modelo conseguiu achar?"**
                    Se em uma foto existem 10 pessoas na rua, e o modelo detecta apenas 7 (e não vê as outras 3), o Recall dele é de **0.70 (70%)**. 
                    Um Recall alto evita que o modelo "deixe passar batido" os objetos.
                </p>
                <div class="didactic-box emerald">
                    <div class="didactic-title"><i data-lucide="scale"></i> O Cabo de Guerra (Precision vs Recall)</div>
                    <div class="didactic-desc">
                        Geralmente eles disputam entre si. Se o modelo for extremamente covarde, ele só desenhará caixas quando tiver 100% de certeza. Sua **Precisão** será altíssima (quase 1.0), mas ele ignorará muitos objetos difíceis, deixando o **Recall** baixo. Se ele for extremamente ousado e chutar qualquer formato estranho na imagem, seu **Recall** será 1.0 (achou tudo), mas ele trará dezenas de alarmes falsos, derrubando a **Precisão**. Um bom modelo equilibra ambos!
                    </div>
                </div>
            </div>

            <div class="topic-section">
                <h3 class="topic-title gold">
                    <i data-lucide="award"></i> mAP50 (Mean Average Precision @ 0.50)
                </h3>
                <p class="guide-text">
                    É a métrica mais importante do YOLO! O mAP50 é a média geral de precisão do modelo considerando que uma caixa é considerada correta se ela cobrir pelo menos **50% da área** do objeto real (IoU >= 0.50). 
                    Valores acima de **0.70 (70%)** já representam um modelo muito inteligente na maioria dos cenários práticos.
                </p>
            </div>

            <div class="topic-section">
                <h3 class="topic-title gold">
                    <i data-lucide="medal"></i> mAP50-95
                </h3>
                <p class="guide-text">
                    O critério de avaliação mais difícil de todos. Ele calcula a precisão média do modelo sob vários níveis de exigência de enquadramento (de 50% de sobreposição de área até 95% de enquadramento perfeito). 
                    Como exige caixas extremamente justas e perfeitas para pontuar alto, qualquer valor de mAP50-95 acima de **0.30 a 0.40** já é considerado excelente!
                </p>
            </div>
        </div>

        <!-- Card 3: Taxa de Aprendizado -->
        <div class="guide-card">
            <h2 class="guide-title">
                <i data-lucide="zap" style="color: var(--accent-cyan)"></i>
                <span>3. Taxa de Aprendizado (Learning Rate - LR)</span>
            </h2>
            
            <div class="indicator-badge indicator-neutral">
                <i data-lucide="refresh-cw"></i> DEVE CAIR DE FORMA CONSTANTE E CONTROLADA
            </div>

            <p class="guide-text">
                A **Taxa de Aprendizado** (ou *Learning Rate*) é uma configuração do treinamento que controla a **velocidade ou tamanho do passo** com que o modelo ajusta seu conhecimento a cada nova imagem vista.
            </p>

            <div class="didactic-box">
                <div class="didactic-title">
                    <i data-lucide="compass"></i> A Metáfora do Escultor
                </div>
                <div class="didactic-desc">
                    Imagine que você vai esculpir uma estátua a partir de um bloco bruto de pedra. 
                    No **início**, você usa uma marreta pesada para tirar lascas gigantescas rapidamente e dar a forma básica (Taxa de Aprendizado alta). 
                    No **final**, para esculpir os olhos, nariz e detalhes da boca, você guarda a marreta e usa um cinzel cirúrgico e delicado com batidas levíssimas (Taxa de Aprendizado baixíssima). Se você usasse a marreta no final, destruiria a estátua!
                </div>
            </div>

            <p class="guide-text">
                Durante o treinamento do Colab, o YOLO reduz a taxa de aprendizado gradualmente (comumente em curva de cosseno). Ele começa alto (ex: `0.01` ou `0.03`) para o modelo se situar rápido no problema e, nas últimas épocas, ela fica extremamente próxima de zero (ex: `0.0001`), permitindo o ajuste milimétrico dos pesos sem arruinar o conhecimento geral consolidado.
            </p>
        </div>

        <!-- Card 4: Como ler a Tabela -->
        <div class="guide-card">
            <h2 class="guide-title">
                <i data-lucide="table" style="color: var(--accent-gold)"></i>
                <span>4. Como Ler o Histórico (A Tabela)</span>
            </h2>
            
            <p class="guide-text">
                A tabela é o boletim escolar completo do seu treinamento. Cada linha representa uma **Época (Epoch)**, que é uma rodada completa de estudo sobre todo o banco de dados.
            </p>

            <ul class="guide-list purple">
                <li><strong>Época (ID)</strong>: Mostra a ordem cronológica do treinamento. A última linha da tabela indica o estado atual do modelo no Colab.</li>
                <li><strong>Tempo Acumulado</strong>: Mostra quanto tempo (em horas, minutos e segundos) o Colab levou para processar até aquela época específica.</li>
                <li><strong>Destaque Dourado 🏆</strong>: A linha dourada na tabela destaca a **Melhor Época** do treinamento. A melhor época é definida como aquela que obteve o maior valor de **mAP50(B)** (o principal indicador de acerto de detecção), usando o **mAP50-95(B)** como critério de desempate. Geralmente, é o modelo desta época específica que você deve baixar e usar para rodar na vida real!</li>
                <li><strong>Box/Cls/DFL Loss (Cores)</strong>: O primeiro valor (branco) é a perda no Treino. O segundo valor (azul ciano) é a perda na prova surpresa de Validação. Busque as épocas onde ambos os valores são os menores possíveis!</li>
            </ul>
        </div>

    </div>

    <!-- Script Lucide -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
