(function () {
    'use strict';

    const config = window.CPG_QUIZ || {};
    const copy = config.copy || {};
    const lang = config.lang || 'en';
    const typeNames = config.typeNames || {};
    const typeIconIds = config.typeIconIds || {};
    const typeIconBaseUrl = config.typeIconBaseUrl || '';
    const pokedexBaseUrl = config.pokedexBaseUrl || `/${lang}`;
    const typeGlyphCache = new Map();

    const generations = {
        all: [1, 1025],
        1: [1, 151],
        2: [152, 251],
        3: [252, 386],
        4: [387, 493],
        5: [494, 649],
        6: [650, 721],
        7: [722, 809],
        8: [810, 905],
        9: [906, 1025],
    };

    const storageKeys = {
        recent: 'cpg_quiz_recent_v1',
        best: 'cpg_quiz_best_v1',
    };

    const elements = {
        setupPanel: document.getElementById('setupPanel'),
        quizSetup: document.getElementById('quizSetup'),
        generationSelect: document.getElementById('generationSelect'),
        startButton: document.getElementById('startButton'),
        bestScoreText: document.getElementById('bestScoreText'),
        loadingPanel: document.getElementById('loadingPanel'),
        errorPanel: document.getElementById('errorPanel'),
        gamePanel: document.getElementById('gamePanel'),
        questionCounter: document.getElementById('questionCounter'),
        liveScore: document.getElementById('liveScore'),
        progressBar: document.getElementById('progressBar'),
        mediaStage: document.getElementById('mediaStage'),
        pokemonNumber: document.getElementById('pokemonNumber'),
        quizImage: document.getElementById('quizImage'),
        cryStage: document.getElementById('cryStage'),
        playCryButton: document.getElementById('playCryButton'),
        cryAudio: document.getElementById('cryAudio'),
        audioError: document.getElementById('audioError'),
        pokemonIdentity: document.getElementById('pokemonIdentity'),
        pokemonIdentityName: document.getElementById('pokemonIdentityName'),
        revealedTypes: document.getElementById('revealedTypes'),
        modeKicker: document.getElementById('modeKicker'),
        questionTitle: document.getElementById('questionTitle'),
        choices: document.getElementById('choices'),
        feedback: document.getElementById('feedback'),
        feedbackTitle: document.getElementById('feedbackTitle'),
        feedbackAnswer: document.getElementById('feedbackAnswer'),
        nextButton: document.getElementById('nextButton'),
        resultPanel: document.getElementById('resultPanel'),
        scoreRing: document.getElementById('scoreRing'),
        finalScore: document.getElementById('finalScore'),
        accuracyText: document.getElementById('accuracyText'),
        resultMessage: document.getElementById('resultMessage'),
        newBestBadge: document.getElementById('newBestBadge'),
        reviewList: document.getElementById('reviewList'),
        replayButton: document.getElementById('replayButton'),
        settingsButton: document.getElementById('settingsButton'),
    };

    let pokemonData = null;
    let dataPromise = null;
    let state = createEmptyState();

    function createEmptyState() {
        return {
            mode: 'mixed',
            generation: 'all',
            count: 10,
            pool: [],
            questions: [],
            questionModes: [],
            index: 0,
            score: 0,
            mistakes: [],
            answered: false,
            correctKey: '',
            correctLabel: '',
        };
    }

    function readStorage(key, fallback) {
        try {
            const value = JSON.parse(localStorage.getItem(key));
            return value === null ? fallback : value;
        } catch (error) {
            return fallback;
        }
    }

    function writeStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            // The quiz remains fully playable when private browsing blocks storage.
        }
    }

    function shuffle(items) {
        const result = items.slice();
        for (let index = result.length - 1; index > 0; index -= 1) {
            const randomIndex = Math.floor(Math.random() * (index + 1));
            [result[index], result[randomIndex]] = [result[randomIndex], result[index]];
        }
        return result;
    }

    function getPokemonName(pokemon) {
        return (pokemon.noms && (pokemon.noms[lang] || pokemon.noms.en)) || `#${pokemon.id}`;
    }

    function slugifyPokemonName(name) {
        return String(name)
            .trim()
            .replace(/♀/g, '-female')
            .replace(/♂/g, '-male')
            .normalize('NFD')
            .replace(/([A-Za-z])[\u0300-\u036f]+/g, '$1')
            .normalize('NFC')
            .toLocaleLowerCase(lang)
            .replace(/ß/g, 'ss')
            .replace(/æ/g, 'ae')
            .replace(/œ/g, 'oe')
            .replace(/ø/g, 'o')
            .replace(/ł/g, 'l')
            .replace(/[^\p{L}\p{N}]+/gu, '-')
            .replace(/^-+|-+$/g, '');
    }

    function getTypeKey(pokemon) {
        return pokemon.types.map((type) => type.slug).sort().join('|');
    }

    function getTypeLabel(pokemon) {
        return pokemon.types
            .map((type) => typeNames[type.slug] || type.slug)
            .join(' / ');
    }

    function createTypeToken(slug, extraClass) {
        const token = document.createElement('span');
        token.className = `type-token type-${slug}${extraClass ? ` ${extraClass}` : ''}`;

        const iconId = typeIconIds[slug];
        if (iconId && typeIconBaseUrl) {
            const icon = document.createElement('img');
            icon.alt = '';
            icon.width = 24;
            icon.height = 24;
            icon.loading = 'eager';
            icon.className = 'type-glyph';
            icon.hidden = true;
            getWhiteTypeGlyph(slug, iconId)
                .then((source) => {
                    icon.src = source;
                    icon.hidden = false;
                })
                .catch(() => icon.remove());
            token.appendChild(icon);
        }

        const label = document.createElement('span');
        label.textContent = typeNames[slug] || slug;
        token.appendChild(label);
        return token;
    }

    function getWhiteTypeGlyph(slug, iconId) {
        if (typeGlyphCache.has(slug)) {
            return typeGlyphCache.get(slug);
        }

        const glyphPromise = new Promise((resolve, reject) => {
            const sourceImage = new Image();
            sourceImage.crossOrigin = 'anonymous';
            sourceImage.onload = () => {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = sourceImage.naturalWidth;
                    canvas.height = sourceImage.naturalHeight;
                    const context = canvas.getContext('2d', { willReadFrequently: true });
                    context.drawImage(sourceImage, 0, 0);
                    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                    const pixels = imageData.data;

                    for (let index = 0; index < pixels.length; index += 4) {
                        const minimumChannel = Math.min(pixels[index], pixels[index + 1], pixels[index + 2]);
                        const whiteAmount = Math.max(0, Math.min(1, (minimumChannel - 180) / 75));
                        pixels[index] = 255;
                        pixels[index + 1] = 255;
                        pixels[index + 2] = 255;
                        pixels[index + 3] = Math.round(pixels[index + 3] * whiteAmount);
                    }

                    context.putImageData(imageData, 0, 0);
                    resolve(canvas.toDataURL('image/png'));
                } catch (error) {
                    reject(error);
                }
            };
            sourceImage.onerror = reject;
            sourceImage.src = `${typeIconBaseUrl}${iconId}.png`;
        });

        typeGlyphCache.set(slug, glyphPromise);
        return glyphPromise;
    }

    function renderTypeTokens(container, slugs, extraClass) {
        container.replaceChildren();
        slugs.forEach((slug) => container.appendChild(createTypeToken(slug, extraClass)));
    }

    function getModeLabel(mode) {
        const labels = {
            mixed: copy.mode_mixed,
            image: copy.mode_image,
            cry: copy.mode_cry,
            type: copy.mode_type,
        };
        return labels[mode] || labels.image;
    }

    function buildQuestionModes(mode, count) {
        if (mode !== 'mixed') {
            return Array(count).fill(mode);
        }

        const result = [];
        const availableModes = ['image', 'cry', 'type'];
        while (result.length < count) {
            result.push(...shuffle(availableModes));
        }
        return result.slice(0, count);
    }

    function getActiveMode() {
        return state.questionModes[state.index] || (state.mode === 'mixed' ? 'image' : state.mode);
    }

    function getQuestionTitle(mode) {
        const titles = {
            image: copy.image_question,
            cry: copy.cry_question,
            type: copy.type_question,
        };
        return titles[mode] || titles.image;
    }

    function getSelectedSettings() {
        const formData = new FormData(elements.quizSetup);
        return {
            mode: formData.get('mode') || 'mixed',
            generation: formData.get('generation') || 'all',
            count: Number(formData.get('count')) || 10,
        };
    }

    function getBestKey(settings) {
        return `${settings.mode}:${settings.generation}:${settings.count}`;
    }

    function updateBestScorePreview() {
        const settings = getSelectedSettings();
        const bestScores = readStorage(storageKeys.best, {});
        const best = bestScores[getBestKey(settings)];

        if (!best) {
            elements.bestScoreText.textContent = copy.no_score;
            return;
        }

        const percentage = Math.round((best.score / best.total) * 100);
        elements.bestScoreText.textContent = `${best.score}/${best.total} · ${percentage}%`;
    }

    function loadPokemonData() {
        if (pokemonData) {
            return Promise.resolve(pokemonData);
        }

        if (!dataPromise) {
            dataPromise = fetch(config.dataUrl, { cache: 'force-cache' })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`Pokédex request failed with ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    if (!Array.isArray(data) || data.length < 4) {
                        throw new Error('Pokédex data is incomplete');
                    }
                    pokemonData = data;
                    return data;
                });
        }

        return dataPromise;
    }

    function getPool(generation) {
        const range = generations[generation] || generations.all;
        return pokemonData.filter((pokemon) => pokemon.id >= range[0] && pokemon.id <= range[1]);
    }

    function chooseQuestions(pool, count) {
        const recentIds = readStorage(storageKeys.recent, []);
        const recentSet = new Set(Array.isArray(recentIds) ? recentIds.map(Number) : []);
        const fresh = shuffle(pool.filter((pokemon) => !recentSet.has(Number(pokemon.id))));
        const selected = fresh.slice(0, count);

        if (selected.length < count) {
            const selectedIds = new Set(selected.map((pokemon) => pokemon.id));
            const supplements = shuffle(pool.filter((pokemon) => !selectedIds.has(pokemon.id)));
            selected.push(...supplements.slice(0, count - selected.length));
        }

        return selected;
    }

    function addRecentPokemon(id) {
        const stored = readStorage(storageKeys.recent, []);
        const recent = Array.isArray(stored) ? stored.map(Number).filter(Number.isFinite) : [];
        const next = [Number(id), ...recent.filter((recentId) => recentId !== Number(id))].slice(0, 80);
        writeStorage(storageKeys.recent, next);
    }

    function createAnswerOptions(target) {
        const activeMode = getActiveMode();
        if (activeMode === 'type') {
            const correctKey = getTypeKey(target);
            const seen = new Set([correctKey]);
            const distractors = [];

            for (const candidate of shuffle(state.pool)) {
                const candidateKey = getTypeKey(candidate);
                if (!seen.has(candidateKey)) {
                    seen.add(candidateKey);
                    distractors.push({
                        key: candidateKey,
                        label: getTypeLabel(candidate),
                        types: candidate.types.map((type) => type.slug),
                    });
                }
                if (distractors.length === 3) {
                    break;
                }
            }

            state.correctKey = correctKey;
            state.correctLabel = getTypeLabel(target);
            return shuffle([{
                key: correctKey,
                label: state.correctLabel,
                types: target.types.map((type) => type.slug),
            }, ...distractors]);
        }

        const correctKey = String(target.id);
        const correctLabel = getPokemonName(target);
        const seenNames = new Set([correctLabel.toLocaleLowerCase(lang)]);
        const distractors = [];

        for (const candidate of shuffle(state.pool)) {
            if (candidate.id === target.id) {
                continue;
            }
            const candidateName = getPokemonName(candidate);
            const normalizedName = candidateName.toLocaleLowerCase(lang);
            if (!seenNames.has(normalizedName)) {
                seenNames.add(normalizedName);
                distractors.push({ key: String(candidate.id), label: candidateName, pokemon: candidate });
            }
            if (distractors.length === 3) {
                break;
            }
        }

        state.correctKey = correctKey;
        state.correctLabel = correctLabel;
        return shuffle([{ key: correctKey, label: correctLabel, pokemon: target }, ...distractors]);
    }

    function showOnly(panel) {
        [elements.setupPanel, elements.loadingPanel, elements.errorPanel, elements.gamePanel, elements.resultPanel]
            .forEach((item) => {
                item.hidden = item !== panel;
            });
    }

    function scrollToQuiz() {
        const top = document.querySelector('.quiz-shell').getBoundingClientRect().top + window.scrollY - 86;
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    }

    async function startGame(settings) {
        state = { ...createEmptyState(), ...settings };
        showOnly(elements.loadingPanel);
        scrollToQuiz();

        try {
            await loadPokemonData();
            state.pool = getPool(state.generation);
            state.count = Math.min(state.count, state.pool.length);
            state.questions = chooseQuestions(state.pool, state.count);
            state.questionModes = buildQuestionModes(state.mode, state.count);
            showOnly(elements.gamePanel);
            renderQuestion();
        } catch (error) {
            showOnly(elements.errorPanel);
        }
    }

    function resetMediaStage(target) {
        const activeMode = getActiveMode();
        elements.quizImage.className = '';
        elements.quizImage.hidden = false;
        elements.quizImage.src = target.image || target.thumbnail;
        elements.quizImage.alt = '';
        elements.pokemonNumber.textContent = `#${String(target.id).padStart(4, '0')}`;
        elements.pokemonNumber.hidden = activeMode !== 'type';
        elements.cryStage.hidden = true;
        elements.audioError.hidden = true;
        elements.cryAudio.pause();
        elements.cryAudio.currentTime = 0;
        elements.cryAudio.removeAttribute('src');
        document.querySelector('.sound-rings').classList.remove('playing');
        elements.pokemonIdentityName.textContent = getPokemonName(target);
        elements.pokemonIdentity.hidden = activeMode !== 'type';
        elements.revealedTypes.hidden = true;
        elements.revealedTypes.replaceChildren();

        if (activeMode === 'image') {
            elements.quizImage.classList.add('silhouette');
        } else if (activeMode === 'cry') {
            elements.quizImage.hidden = true;
            elements.cryStage.hidden = false;
            elements.cryAudio.src = (target.cris && (target.cris.moderne || target.cris.retro)) || '';
            elements.playCryButton.querySelector('strong').textContent = copy.play_cry;
        }
    }

    function renderQuestion() {
        const target = state.questions[state.index];
        const activeMode = getActiveMode();
        state.answered = false;
        elements.questionCounter.textContent = `${copy.question_progress} ${state.index + 1} / ${state.count}`;
        elements.liveScore.textContent = `${copy.score} : ${state.score}`;
        elements.progressBar.style.width = `${(state.index / state.count) * 100}%`;
        elements.modeKicker.textContent = getModeLabel(activeMode);
        elements.questionTitle.textContent = getQuestionTitle(activeMode);
        elements.feedback.hidden = true;
        elements.feedback.classList.remove('incorrect-feedback');
        elements.choices.replaceChildren();
        elements.choices.className = `choices choices-${activeMode}`;
        resetMediaStage(target);

        const options = createAnswerOptions(target);
        options.forEach((option) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'choice-button';
            button.dataset.answerKey = option.key;
            if (activeMode === 'type') {
                button.classList.add('type-choice');
                const typeOption = document.createElement('span');
                typeOption.className = 'type-option';
                option.types.forEach((slug) => typeOption.appendChild(createTypeToken(slug, 'choice-type-token')));
                button.appendChild(typeOption);
                button.setAttribute('aria-label', option.label);
            } else if (activeMode === 'cry') {
                button.classList.add('pokemon-choice', 'cry-choice');
                const image = document.createElement('img');
                image.src = option.pokemon.thumbnail || option.pokemon.image;
                image.alt = '';
                image.width = 72;
                image.height = 72;
                image.loading = 'eager';
                const name = document.createElement('span');
                name.textContent = option.label;
                button.append(image, name);
            } else {
                button.textContent = option.label;
            }
            button.addEventListener('click', () => answerQuestion(option, button));
            elements.choices.appendChild(button);
        });

        window.setTimeout(() => {
            const firstChoice = elements.choices.querySelector('button');
            if (firstChoice) {
                firstChoice.focus({ preventScroll: true });
            }
        }, 80);
    }

    function revealPokemon(target) {
        if (getActiveMode() === 'cry') {
            elements.cryAudio.pause();
            document.querySelector('.sound-rings').classList.remove('playing');
            elements.cryStage.hidden = true;
            elements.quizImage.hidden = false;
        }
        elements.quizImage.classList.remove('silhouette');
        elements.quizImage.classList.add('revealed');
        elements.quizImage.alt = getPokemonName(target);
        elements.pokemonNumber.hidden = false;
        elements.pokemonIdentityName.textContent = getPokemonName(target);
        elements.pokemonIdentity.hidden = false;
        renderTypeTokens(
            elements.revealedTypes,
            target.types.map((type) => type.slug),
            'revealed-type-token'
        );
        elements.revealedTypes.hidden = false;
    }

    function answerQuestion(option, selectedButton) {
        if (state.answered) {
            return;
        }

        state.answered = true;
        const target = state.questions[state.index];
        const isCorrect = option.key === state.correctKey;
        const buttons = Array.from(elements.choices.querySelectorAll('button'));

        buttons.forEach((button) => {
            button.disabled = true;
            if (button.dataset.answerKey === state.correctKey) {
                button.classList.add('correct');
            } else if (button === selectedButton) {
                button.classList.add('incorrect');
            } else {
                button.classList.add('dimmed');
            }
        });

        if (isCorrect) {
            state.score += 1;
            elements.feedbackTitle.textContent = copy.correct;
        } else {
            state.mistakes.push({
                pokemon: target,
                selectedLabel: option.label,
                correctLabel: state.correctLabel,
            });
            elements.feedbackTitle.textContent = copy.incorrect;
            elements.feedback.classList.add('incorrect-feedback');
        }

        addRecentPokemon(target.id);
        revealPokemon(target);
        elements.liveScore.textContent = `${copy.score} : ${state.score}`;
        elements.progressBar.style.width = `${((state.index + 1) / state.count) * 100}%`;
        elements.feedbackAnswer.textContent = `${copy.correct_answer} ${state.correctLabel}`;
        elements.nextButton.textContent = state.index + 1 === state.count ? copy.see_results : copy.next;
        elements.feedback.hidden = false;
        elements.nextButton.focus({ preventScroll: true });
    }

    function getResultMessage(percentage) {
        if (percentage === 100) {
            return copy.result_perfect;
        }
        if (percentage >= 80) {
            return copy.result_great;
        }
        if (percentage >= 50) {
            return copy.result_good;
        }
        return copy.result_retry;
    }

    function saveBestScore() {
        const bestScores = readStorage(storageKeys.best, {});
        const key = getBestKey(state);
        const previous = bestScores[key];
        const isNewBest = !previous || state.score > previous.score;

        if (isNewBest) {
            bestScores[key] = {
                score: state.score,
                total: state.count,
                savedAt: new Date().toISOString(),
            };
            writeStorage(storageKeys.best, bestScores);
        }

        return isNewBest;
    }

    function createReviewItem(mistake) {
        const item = document.createElement('article');
        item.className = 'review-item';

        const image = document.createElement('img');
        image.src = mistake.pokemon.thumbnail || mistake.pokemon.image;
        image.alt = '';
        image.loading = 'lazy';

        const content = document.createElement('div');
        const title = document.createElement('h4');
        title.textContent = getPokemonName(mistake.pokemon);
        const detail = document.createElement('p');
        detail.textContent = `${copy.your_answer}: ${mistake.selectedLabel} · ${copy.correct_answer} ${mistake.correctLabel}`;
        content.append(title, detail);

        const link = document.createElement('a');
        const localizedName = getPokemonName(mistake.pokemon);
        link.href = `${pokedexBaseUrl}/${encodeURIComponent(slugifyPokemonName(localizedName))}`;
        link.textContent = copy.open_entry;

        item.append(image, content, link);
        return item;
    }

    function renderResults() {
        showOnly(elements.resultPanel);
        const percentage = Math.round((state.score / state.count) * 100);
        const isNewBest = saveBestScore();

        elements.finalScore.textContent = `${state.score}/${state.count}`;
        elements.accuracyText.textContent = `${copy.accuracy} · ${percentage}%`;
        elements.scoreRing.style.setProperty('--score-angle', `${percentage * 3.6}deg`);
        elements.resultMessage.textContent = getResultMessage(percentage);
        elements.newBestBadge.hidden = !isNewBest;
        elements.reviewList.replaceChildren();

        if (state.mistakes.length === 0) {
            const message = document.createElement('p');
            message.className = 'perfect-message';
            message.textContent = copy.perfect_review;
            elements.reviewList.appendChild(message);
        } else {
            state.mistakes.forEach((mistake) => {
                elements.reviewList.appendChild(createReviewItem(mistake));
            });
        }

        updateBestScorePreview();
        scrollToQuiz();
        document.getElementById('resultTitle').focus?.({ preventScroll: true });
    }

    function handlePlayCry() {
        elements.audioError.hidden = true;
        elements.cryAudio.currentTime = 0;
        const playPromise = elements.cryAudio.play();
        document.querySelector('.sound-rings').classList.add('playing');
        elements.playCryButton.querySelector('strong').textContent = copy.replay_cry;

        if (playPromise) {
            playPromise.catch(() => {
                document.querySelector('.sound-rings').classList.remove('playing');
                elements.audioError.hidden = false;
            });
        }
    }

    elements.quizSetup.addEventListener('submit', (event) => {
        event.preventDefault();
        startGame(getSelectedSettings());
    });

    elements.quizSetup.addEventListener('change', updateBestScorePreview);

    elements.playCryButton.addEventListener('click', handlePlayCry);
    elements.cryAudio.addEventListener('ended', () => {
        document.querySelector('.sound-rings').classList.remove('playing');
    });
    elements.cryAudio.addEventListener('error', () => {
        document.querySelector('.sound-rings').classList.remove('playing');
        elements.audioError.hidden = false;
    });

    elements.nextButton.addEventListener('click', () => {
        if (state.index + 1 >= state.count) {
            renderResults();
            return;
        }
        state.index += 1;
        renderQuestion();
    });

    elements.replayButton.addEventListener('click', () => {
        startGame({ mode: state.mode, generation: state.generation, count: state.count });
    });

    elements.settingsButton.addEventListener('click', () => {
        showOnly(elements.setupPanel);
        updateBestScorePreview();
        scrollToQuiz();
    });

    updateBestScorePreview();
    loadPokemonData().catch(() => {
        // The visible error state is shown only if the user starts a game.
    });
}());
