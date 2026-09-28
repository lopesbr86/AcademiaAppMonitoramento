# 🏋️ GymUp

### Plataforma Colaborativa para Monitoramento de Lotação em Academias

> **Chegar na academia e encontrar tudo lotado nunca mais.**
> O GymUp é feito por alunos, para alunos: cada pessoa que treina ajuda a mostrar, em tempo real, como está o movimento da academia.

---

## 💡 Sobre o projeto

A busca pela musculação e pelo treino funcional cresce a cada ano no Brasil, e com ela vem um problema conhecido de quem treina: a **superlotação em horários de pico**. Quem segue uma rotina estruturada, com sessões cronometradas ou treinos de alto volume, perde ritmo, tempo de descanso e rendimento ao descobrir só na chegada que os equipamentos estão ocupados.

O **GymUp** resolve isso com **colaboração**. Os próprios alunos informam o nível de lotação da academia, e todos os outros usuários consultam essa informação antes de sair de casa, sem perder tempo com deslocamento à toa.

## ✨ Funcionalidades

- 📍 **Relato de lotação em tempo real**: informe se a academia está com lotação **baixa**, **média** ou **alta** por meio de uma interface simples e intuitiva.
- 🛰️ **Validação de presença por geolocalização**: a API de geolocalização do celular confirma que quem relata está mesmo no local, o que dá mais confiança aos dados.
- 🔎 **Consulta do status atual**: veja como está cada academia antes de treinar.
- 📊 **Médias históricas por horário**: descubra os melhores horários para treinar, de acordo com o movimento habitual.
- ⏱️ **Registro de treino com temporizador**: acompanhe a duração dos seus treinos.
- 🏆 **Gamificação e ranking**: quem colabora ganha pontos e sobe no ranking da comunidade.
- 🏢 **Cadastro de academias**: a comunidade pode inserir novas academias na plataforma.

## 📱 Por que um PWA?

O GymUp é um **Progressive Web App**: um único código-fonte que funciona em **Android, iOS, Windows, macOS e Linux**, com cara e comportamento de aplicativo nativo. Assim, o app fica no bolso do aluno **sem passar por lojas de aplicativos** e sem burocracia de instalação, com suporte a funcionamento offline por meio de *Service Workers*.

## 🛠️ Tecnologias

| Camada | Tecnologia | Uso no projeto |
|---|---|---|
| Estrutura | **HTML5** | Estrutura de todas as telas |
| Estilo | **CSS3** | Layout responsivo em formato de app mobile |
| Front-end | **JavaScript** | Geolocalização, temporizador de treino, interface dinâmica e registro do Service Worker |
| Back-end | **PHP** | Lógica de negócio, autenticação, cálculo de lotação e gamificação |
| Banco de dados | **MySQL** | Usuários, histórico de lotação, treinos e pontuação |
| PWA | **Service Workers + Cache API** | Instalação na tela inicial e funcionamento offline |

## 🗂️ Modelagem

O sistema foi modelado com diagramas de caso de uso, de classes e modelo conceitual/lógico. O banco de dados `gymup_db` é composto pelas seguintes entidades:

- **Usuario**: dados de acesso e pontos de gamificação
- **Academia**: nome, endereço, coordenadas e status de lotação atual
- **RegistroLotacao**: nível de lotação informado, data/hora e validação de presença
- **Treino**: início e duração dos treinos realizados
- **Gamificacao**: pontos acumulados e posição no ranking

## ✅ Requisitos para uso

| | Mínimo | Recomendado |
|---|---|---|
| **Android** | Android 7.0+ (Chrome 90+, Firefox 90+ ou Samsung Internet) | Android 11+ com Chrome/Edge atualizado |
| **iOS** | iOS 14+ (Safari 14+) | iOS 16.4+ (Safari 16.4+, com suporte completo a Push Notifications) |
| **Memória RAM** | 2 GB | 4 GB ou mais |
| **Armazenamento** | 50 MB livres | 150 MB livres |
| **Conexão** | 3G/4G ou Wi-Fi | 4G/5G ou Wi-Fi estável |

## 🗓️ Roadmap (Sprints)

- [x] **Sprint 1**: planejamento do protótipo (requisitos, telas, banco de dados)
- [x] **Sprint 2**: visualização de academias (tela inicial, listagem e detalhes)
- [x] **Sprint 3**: função principal (informação de lotação: baixa/média/alta, exibição na lista e nos detalhes, atualização)
- [ ] **Sprint 4**: finalização (testes, correções, melhoria das interfaces, dados das academias e documentação)

## 🚀 Como rodar localmente

```bash
# 1. Clone o repositório
git clone https://github.com/<seu-usuario>/<nome-do-repositorio>.git

# 2. Crie o banco de dados no MySQL
#    (o script cria o gymup_db caso ele não exista)

# 3. Configure a conexão com o banco nos arquivos PHP

# 4. Sirva o projeto com PHP + MySQL (XAMPP, WAMP, Laragon ou similar)
#    e acesse pelo navegador do celular ou do computador
```

> ⚠️ Ajuste os passos acima conforme a estrutura do repositório de vocês.

## 👥 Equipe

Projeto desenvolvido como **Trabalho de Conclusão de Curso** do curso **Técnico em Desenvolvimento de Sistemas (EaD)**, sob orientação da **Profª. Tatiana Carla de Mattos Valério Monteiro**, seguindo a metodologia ágil **Scrum**.

| Integrante | Papel | Responsabilidades |
|---|---|---|
| **Lydia Silva de Aquino** | Scrum Master | Facilitação ágil, remoção de impedimentos e testes de infraestrutura/PWA |
| **Júlia Ribeiro de Souza** | Product Owner | Gestão do backlog, modelagem MySQL e integração PHP/MySQL |
| **Bruna Fernandes Lopes** | Desenvolvedora | Interface (UI/UX), prototipagem, HTML e CSS |
| **Luísa Marçal Leandro Fernandes** | Desenvolvedora | JavaScript, PWA (Service Workers, Cache API) e API de geolocalização |
| **Danilo José Ferreira Domingos Atanasio** | Desenvolvedor | Lógica de negócio em PHP, autenticação, cálculo de lotação e gamificação |

## 📚 Referências

- SOMMERVILLE, Ian. *Engenharia de Software*. 9. ed. Pearson, 2011.
- MDN Web Docs: [Progressive Web Apps](https://developer.mozilla.org/pt-BR/docs/Web/Progressive_web_apps), [Geolocation API](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API) e [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- W3C: [Geolocation API Specification](https://www.w3.org/TR/geolocation/)

---

<p align="center">Feito com 💙 e muito treino pelo <b>Grupo 7</b></p>