-----Uso de IA Generativa-------
A nivel de transparencia, quero especificar onde a IA foi usada e de que forma.

BACK

No BACK, como é o foco do teste, não utilizei IA em praticamente nada, so utilizei em 2 aspectos que foi o uso do RULE no validator, pq é relativamente novo e não sabia muito bem como aplicar com o unique, dito isso entendi o conceito.

E pra criar os testes em PEST, como so utilizei PEST por um curto tempo, não sabia muito bem como aplicar as formas mais corretas no uso, então, fui usnado IA pra compreender melhor como funcionada, mas eu escrevi o codigo, foi so pra compreender como funcionava, eu tenho mais experiencia com o PHPUnit, então nao foi dificl, ja que são muito parecidos.

Outro detalhe, criei as traducoes com IA tb, pq nao lembro todos os cases de erros do laravel e é algo simples.

FRONT

Como o teste especifica mais a questao do laravel e docker, cerca de uns 80% do FRONT foi feito com IA, Basicamente usei pra fazer toda a parte visual.

Não escrevi 1 linha de CSS, pq ninguem mereçe. Não sou muito fã de CSS então so mexo mesmo qunado é extremamente necessario, CSS é um caso de uso muito bom pra IA tambem.

Para o VUE, foi gerado praticamente toda a parte de navbar, sidebar, e o visual do dashboard, a parte de produtos eu fiz boa parte, listagem de produtos, adicionar produtos, gerar os 30 pordutos, outras a IA fez, tipo o search e a paginação do front.

ChatBot da IA, a parte Visual tambem foi totalmente gerado, Parte do back eu fiz manualmente, pedi ajuda so na constrrução prompt, pra otimizar o retorno da resposta da IA.
----------------------------

-----Descrição do projeto------

Stack - Laravel 12, Vue.js, Postgres

Projeto tem em vista um sistema de produtos, tentei manter o mais fiel possivel as especificações, mas adicionando alguns detalhes que achei valido, como por exemplo o user_id no produtos, ja que tem autenticação, nao queremos que
mostre todos os produtos do banco pra todas as pessoas. especifiquei 12 cadas decimais para os valores de preço. Adicionei informaçoes como, preco de custo e preco de venda, meu objetivo foi pegar mais informações pra fazer o dashboard e tambem para allimentar mais dados para a IA, Apliquei alguns principios da SOLID, como é um projeto simples não tinha muitos casos de uso, mas fiz principalmente os Services de inserts no banco e para validação de dados. 
Requisitos especificaram o uso de API Rest, eu me passei um pouco nisso pq tava querendo usar o INERTIA e nao me atentei, o INERTIA tem aspectos de REST, mas não se pode chamar ele de API REST, dito isso a nivel de demonstração, fiz a estrutura de API via REST, ta no arquivo API.php, e usei a parte de comunicação com a IA para usar de exemplo.

Autenticação foi feita pelo Auth padrão do Laravel.
Docker foi feito usando o Laravel:Sail
Testes Automatizados foram feitos com PEST (Depois que vi que era pra ser PHPUnit, não sei pq mas estava com o PEST na cabeca, dito isso tenho mais experiencia usando o Unit)
CHATBOT de IA, não foi pedido, dito isso como estou estudando bastante integração de IA em aplicações, achei que seria um toque a mais, e aproveitaria e iria ajudar nos meus estudos


---Como rodar a aplicação---
Rodar o docker pode precisar de algumas configurações, então dificil cobrir todos os possiveis erros que pode acontecer, mas vou fazer passo a passo como fiz aqui

imagino que tenha as dependecias instaladas, tipo docker, php, composer, git

git clone https://github.com/AlexmarJr/FD-Products.git

va na pasta do projeto e rode o composer install

copie o env.example e renomeio pra env "cp .env.example .env"

php artisan key:generate

./vendor/bin/sail up -d

./vendor/bin/sail npm install

./vendor/bin/sail artisan migrate
// lembrando que to usando a porta 5431 pra evitar conflitos com outras instancias do posgres

./vendor/bin/sail npm run dev
Caso de algum problema com o alpine, troque no arquivo docker-compose.yml e altere o alpine 18 para 17 - image: 'postgres:17-alpine'

./vendor/bin/sail artisan db:seed 

Depois disso ja deve ta com o projeto em pe em localhost, caso tenha mais dificuldades, pode mandar uma messagem pra mim que da pra vermos juntos. 
Como plano B, subi a aplicação no Fly, então da pra testar direto la - https://firstdecision.fly.dev/
o banco ta na versão free, então a primeira interação pode ter um delay ate acordar as maquinas, depois deve ta de boa.


----- Potenciais melhorias na IA -----

Atualmente eu pego todo o estoque do usuario e envio no prompt, oq é totalmente não escalavel e ineficiente, caso tenha uma lista grande de produtos a IA pode travar no limite de contexto, ou pior, pode passar e gastar 2 reais na requicisão, a forma correta de abordar essa situação seria uma RAG dinamica, basicamente criar arquivos parecidos com uma view de banco com o id do usuario, e mandar ele fazer buscas de estoque nesse arquivo, ai sempre que tiver uma alteração nos produtos pode atualizar, caso seja muitos usuarios, uma cron pode ser feita praa tualizar esses dados de tempo em tempo.


------Atençao-----
Caso a API do chat da IA esteja voltando com erro, tava tendo uma instabilidade no modelo 3.5-flash, que tava retornando um 503, realisticamente, fazer um fallback pra outros modelos seria o caminho correto, mas ja ta meio tarde, e tambem mudar pro 3.1 pro é zuado, fiz um teste aqui e mandei um "Olá", foi 30 centavos; Ai caso esteja dando esse problema, me avisa que troco o modelo rapidinho

Pra trocar o modelo local voce precisa criar uma chave no seu .env chamada GEMINI_API, e em AiChatApiController Va nessa linha aq 
'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=' . $geminiApiKey,
troque o gemini-3.5-flash por gemini-3.1-pro-preview ou gemini-2.5-flash. Vou mandar a chave da API por Email, se eu botar aq o Google vai brikar minha chave.\


Esqueci os links do drive - https://drive.google.com/drive/folders/1I6u6TDzstR0WhELgN30-cy-DmrOjJpP-