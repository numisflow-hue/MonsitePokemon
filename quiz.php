<?php
$allowed_langs = ['fr', 'en', 'es', 'de', 'it', 'ja'];
$quiz_request_path = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/quiz.php', PHP_URL_PATH);
$quiz_path_parts = array_values(array_filter(explode('/', trim(rawurldecode((string) $quiz_request_path), '/')), 'strlen'));
$quiz_route_lang = count($quiz_path_parts) === 2
    && $quiz_path_parts[1] === 'quiz'
    && in_array($quiz_path_parts[0], $allowed_langs, true)
        ? $quiz_path_parts[0]
        : null;
$requested_lang = isset($_GET['lang']) && is_string($_GET['lang']) ? $_GET['lang'] : 'en';
$query_lang = in_array($requested_lang, $allowed_langs, true) ? $requested_lang : 'en';
$lang = $quiz_route_lang !== null ? $quiz_route_lang : $query_lang;
$canonical_quiz_url = '/' . rawurlencode($lang) . '/quiz';
$current_quiz_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/quiz.php';

if ($current_quiz_url !== $canonical_quiz_url) {
    header('Location: ' . $canonical_quiz_url, true, 301);
    exit;
}

$lang_names = [
    'fr' => 'Français',
    'en' => 'English',
    'es' => 'Español',
    'de' => 'Deutsch',
    'it' => 'Italiano',
    'ja' => '日本語',
];

$translations = [
    'fr' => [
        'page_title' => 'Quiz Pokémon | CoolPokemonGames',
        'pokedex' => 'Pokédex',
        'quizzes' => 'Quiz',
        'eyebrow' => 'Centre d’entraînement des chasseurs Pokémon',
        'hero_title' => 'Observe, écoute… et attrape la bonne réponse !',
        'hero_intro' => 'Enfile ta casquette de Dresseur : des Pokémon se cachent dans les images, les cris et les types. À toi de les retrouver !',
        'hero_badge_pokemon' => 'Pokémon à découvrir',
        'hero_badge_challenges' => 'défis différents',
        'hero_badge_types' => 'types à maîtriser',
        'setup_kicker' => 'Ta prochaine mission',
        'setup_title' => 'Prépare ta chasse',
        'setup_intro' => 'Choisis ton défi, ton terrain et la durée de ta mission. Les Pokémon changent à chaque partie !',
        'mode_label' => 'Choisis ton défi',
        'mode_mixed' => 'Un peu de tout',
        'mode_mixed_desc' => 'Images, cris et types : une surprise à chaque question !',
        'mode_image' => 'Qui est ce Pokémon ?',
        'mode_image_desc' => 'Repère sa silhouette avant qu’il ne s’échappe.',
        'mode_cry' => 'Poké Blindtest',
        'mode_cry_desc' => 'Tends l’oreille et retrouve le bon Pokémon.',
        'mode_type' => 'Maître des types',
        'mode_type_desc' => 'Décode ses badges de type comme un vrai expert.',
        'generation_label' => 'Choisis ton terrain de chasse',
        'all_generations' => 'Le monde Pokémon entier',
        'generation' => 'Génération',
        'length_label' => 'Fixe la durée de ta mission',
        'questions' => 'questions',
        'start' => 'Lancer la chasse !',
        'best_score' => 'Ton meilleur trophée',
        'no_score' => 'À toi d’établir le premier record !',
        'loading' => 'Les Pokémon se cachent…',
        'load_error' => 'Impossible de charger les données du Pokédex. Réessaie après avoir actualisé la page.',
        'question_progress' => 'Étape',
        'score' => 'Poképoints',
        'image_question' => 'Quel est ce Pokémon ?',
        'cry_question' => 'À qui appartient ce cri ?',
        'type_question' => 'Quels sont les types de ce Pokémon ?',
        'play_cry' => 'Écouter le cri',
        'replay_cry' => 'Réécouter le cri',
        'audio_error' => 'Le cri n’a pas pu être lu. Tu peux réessayer.',
        'correct' => 'Bien joué, Dresseur !',
        'incorrect' => 'Oups, il s’est échappé !',
        'correct_answer' => 'La bonne réponse était :',
        'next' => 'Continuer la chasse',
        'see_results' => 'Découvrir mon trophée',
        'result_kicker' => 'Bilan de ta mission',
        'result_title' => 'Mission accomplie !',
        'result_perfect' => 'Tous trouvés ! Ton instinct de chasseur Pokémon est imbattable.',
        'result_great' => 'Super mission ! Tu es tout près du rang de Maître Pokémon.',
        'result_good' => 'Belle chasse ! Mémorise les indices manqués et repars gagner des Poképoints.',
        'result_retry' => 'Tout grand Dresseur commence par s’entraîner. Le Pokédex va t’aider pour ta prochaine mission !',
        'accuracy' => 'Pokémon trouvés',
        'new_best' => 'Nouveau record !',
        'review_title' => 'Ces Pokémon t’attendent encore',
        'review_intro' => 'Ouvre leur fiche, mémorise leurs indices et retrouve-les à la prochaine mission.',
        'your_answer' => 'Ta réponse',
        'open_entry' => 'Voir la fiche',
        'replay' => 'Repartir en mission',
        'change_settings' => 'Choisir une autre mission',
        'perfect_review' => 'Tous trouvés ! Cette mission était parfaite.',
        'noscript' => 'JavaScript doit être activé pour jouer aux quiz.',
    ],
    'en' => [
        'page_title' => 'Pokémon Quiz | CoolPokemonGames',
        'pokedex' => 'Pokédex',
        'quizzes' => 'Quizzes',
        'eyebrow' => 'Pokémon Hunters Training Center',
        'hero_title' => 'Look, listen… and catch the right answer!',
        'hero_intro' => 'Put on your Trainer cap: Pokémon are hiding in pictures, cries and types. It is up to you to find them!',
        'hero_badge_pokemon' => 'Pokémon to discover',
        'hero_badge_challenges' => 'different challenges',
        'hero_badge_types' => 'types to master',
        'setup_kicker' => 'Your next mission',
        'setup_title' => 'Prepare your hunt',
        'setup_intro' => 'Choose your challenge, your territory and the length of your mission. The Pokémon change every game!',
        'mode_label' => 'Choose your challenge',
        'mode_mixed' => 'A bit of everything',
        'mode_mixed_desc' => 'Pictures, cries and types mixed at random.',
        'mode_image' => 'Who’s That Pokémon?',
        'mode_image_desc' => 'Recognize its silhouette and official artwork.',
        'mode_cry' => 'Poké Blindtest',
        'mode_cry_desc' => 'Whose cry is this?',
        'mode_type' => 'Type Master',
        'mode_type_desc' => 'Find the exact type combination.',
        'generation_label' => 'Choose your hunting territory',
        'all_generations' => 'All generations',
        'generation' => 'Generation',
        'length_label' => 'Set the length of your mission',
        'questions' => 'questions',
        'start' => 'Start the hunt!',
        'best_score' => 'Your best trophy',
        'no_score' => 'Set the first record!',
        'loading' => 'The Pokémon are hiding…',
        'load_error' => 'The Pokédex data could not be loaded. Refresh the page and try again.',
        'question_progress' => 'Question',
        'score' => 'Score',
        'image_question' => 'Which Pokémon is this?',
        'cry_question' => 'Whose cry is this?',
        'type_question' => 'What are this Pokémon’s types?',
        'play_cry' => 'Play the cry',
        'replay_cry' => 'Play the cry again',
        'audio_error' => 'The cry could not be played. You can try again.',
        'correct' => 'Correct!',
        'incorrect' => 'Not quite.',
        'correct_answer' => 'The correct answer was:',
        'next' => 'Next question',
        'see_results' => 'See my results',
        'result_kicker' => 'Mission report',
        'result_title' => 'Mission complete!',
        'result_perfect' => 'Amazing: a perfect score! Your mental Pokédex is formidable.',
        'result_great' => 'Great game! You are only a few answers away from perfection.',
        'result_good' => 'Good start. Review your misses and try again to improve.',
        'result_retry' => 'Every Trainer starts somewhere. Review the answers and head out again!',
        'accuracy' => 'Accuracy',
        'new_best' => 'New record!',
        'review_title' => 'Review in the Pokédex',
        'review_intro' => 'Open an entry to turn every mistake into new knowledge.',
        'your_answer' => 'Your answer',
        'open_entry' => 'Open entry',
        'replay' => 'Replay with these settings',
        'change_settings' => 'Change settings',
        'perfect_review' => 'No mistakes to review. Well done!',
        'noscript' => 'JavaScript must be enabled to play the quizzes.',
    ],
    'es' => [
        'page_title' => 'Quiz Pokémon | CoolPokemonGames',
        'pokedex' => 'Pokédex', 'quizzes' => 'Quiz', 'eyebrow' => 'Centro de entrenamiento de cazadores Pokémon',
        'hero_title' => '¡Observa, escucha y atrapa la respuesta correcta!',
        'hero_intro' => 'Ponte la gorra de Entrenador: los Pokémon se esconden en imágenes, gritos y tipos. ¡Encuéntralos!',
        'hero_badge_pokemon' => 'Pokémon por descubrir', 'hero_badge_challenges' => 'retos diferentes', 'hero_badge_types' => 'tipos por dominar',
        'setup_kicker' => 'Tu próxima misión', 'setup_title' => 'Prepara tu caza',
        'setup_intro' => 'Elige tu reto, tu territorio y la duración de la misión. ¡Los Pokémon cambian en cada partida!',
        'mode_label' => 'Elige tu reto', 'mode_mixed' => 'Un poco de todo', 'mode_mixed_desc' => 'Imágenes, gritos y tipos mezclados al azar.',
        'mode_image' => '¿Quién es este Pokémon?', 'mode_image_desc' => 'Reconoce su silueta y su ilustración.',
        'mode_cry' => 'Poké Blindtest', 'mode_cry_desc' => '¿De quién es este grito?',
        'mode_type' => 'Maestro de tipos', 'mode_type_desc' => 'Encuentra la combinación exacta de tipos.',
        'generation_label' => 'Elige tu territorio de caza', 'all_generations' => 'Todas las generaciones', 'generation' => 'Generación',
        'length_label' => 'Elige la duración de tu misión', 'questions' => 'preguntas', 'start' => '¡Iniciar la caza!',
        'best_score' => 'Tu mejor trofeo', 'no_score' => '¡Consigue el primer récord!',
        'loading' => 'Los Pokémon se esconden…', 'load_error' => 'No se pudieron cargar los datos de la Pokédex. Actualiza la página e inténtalo de nuevo.',
        'question_progress' => 'Pregunta', 'score' => 'Puntos', 'image_question' => '¿Qué Pokémon es?',
        'cry_question' => '¿De quién es este grito?', 'type_question' => '¿Cuáles son los tipos de este Pokémon?',
        'play_cry' => 'Escuchar el grito', 'replay_cry' => 'Volver a escuchar', 'audio_error' => 'No se pudo reproducir el grito. Puedes intentarlo de nuevo.',
        'correct' => '¡Respuesta correcta!', 'incorrect' => 'No exactamente.', 'correct_answer' => 'La respuesta correcta era:',
        'next' => 'Continuar la caza', 'see_results' => 'Ver mi trofeo', 'result_kicker' => 'Informe de misión', 'result_title' => '¡Misión cumplida!',
        'result_perfect' => '¡Increíble, puntuación perfecta! Tu Pokédex mental es formidable.',
        'result_great' => '¡Gran partida! Te falta muy poco para la perfección.', 'result_good' => 'Buen comienzo. Revisa tus errores e inténtalo otra vez.',
        'result_retry' => 'Todo Entrenador empieza en algún lugar. ¡Repasa y vuelve a la aventura!',
        'accuracy' => 'Aciertos', 'new_best' => '¡Nuevo récord!', 'review_title' => 'Repasar en la Pokédex',
        'review_intro' => 'Abre una ficha para convertir cada error en conocimiento.', 'your_answer' => 'Tu respuesta',
        'open_entry' => 'Ver ficha', 'replay' => 'Jugar de nuevo', 'change_settings' => 'Cambiar ajustes',
        'perfect_review' => 'No hay errores que repasar. ¡Enhorabuena!', 'noscript' => 'JavaScript debe estar activado para jugar.',
    ],
    'de' => [
        'page_title' => 'Pokémon-Quiz | CoolPokemonGames',
        'pokedex' => 'Pokédex', 'quizzes' => 'Quiz', 'eyebrow' => 'Trainingszentrum für Pokémon-Jäger',
        'hero_title' => 'Schau hin, hör zu und fang die richtige Antwort!',
        'hero_intro' => 'Setz deine Trainer-Kappe auf: Pokémon verstecken sich in Bildern, Rufen und Typen. Finde sie!',
        'hero_badge_pokemon' => 'Pokémon zu entdecken', 'hero_badge_challenges' => 'verschiedene Aufgaben', 'hero_badge_types' => 'Typen zu meistern',
        'setup_kicker' => 'Deine nächste Mission', 'setup_title' => 'Bereite deine Jagd vor',
        'setup_intro' => 'Wähle Herausforderung, Gebiet und Missionslänge. Die Pokémon wechseln in jedem Spiel!',
        'mode_label' => 'Wähle deine Herausforderung', 'mode_mixed' => 'Von allem etwas', 'mode_mixed_desc' => 'Bilder, Rufe und Typen zufällig gemischt.',
        'mode_image' => 'Wer ist dieses Pokémon?', 'mode_image_desc' => 'Erkenne Silhouette und Illustration.',
        'mode_cry' => 'Poké Blindtest', 'mode_cry_desc' => 'Wessen Ruf ist das?',
        'mode_type' => 'Typen-Meister', 'mode_type_desc' => 'Finde die genaue Typenkombination.',
        'generation_label' => 'Wähle dein Jagdgebiet', 'all_generations' => 'Alle Generationen', 'generation' => 'Generation',
        'length_label' => 'Bestimme die Missionslänge', 'questions' => 'Fragen', 'start' => 'Jagd starten!',
        'best_score' => 'Deine beste Trophäe', 'no_score' => 'Stelle den ersten Rekord auf!',
        'loading' => 'Die Pokémon verstecken sich…', 'load_error' => 'Die Pokédex-Daten konnten nicht geladen werden. Bitte aktualisiere die Seite.',
        'question_progress' => 'Frage', 'score' => 'Punkte', 'image_question' => 'Welches Pokémon ist das?',
        'cry_question' => 'Zu welchem Pokémon gehört dieser Ruf?', 'type_question' => 'Welche Typen hat dieses Pokémon?',
        'play_cry' => 'Ruf anhören', 'replay_cry' => 'Ruf erneut anhören', 'audio_error' => 'Der Ruf konnte nicht abgespielt werden. Versuche es erneut.',
        'correct' => 'Richtig!', 'incorrect' => 'Nicht ganz.', 'correct_answer' => 'Die richtige Antwort war:',
        'next' => 'Jagd fortsetzen', 'see_results' => 'Trophäe ansehen', 'result_kicker' => 'Missionsbericht', 'result_title' => 'Mission erfüllt!',
        'result_perfect' => 'Unglaublich: fehlerfrei! Dein innerer Pokédex ist beeindruckend.',
        'result_great' => 'Tolles Spiel! Nur noch wenige Antworten bis zur Perfektion.', 'result_good' => 'Guter Anfang. Sieh dir deine Fehler an und versuche es erneut.',
        'result_retry' => 'Jeder Trainer fängt einmal an. Lerne aus den Antworten und starte neu!',
        'accuracy' => 'Trefferquote', 'new_best' => 'Neuer Rekord!', 'review_title' => 'Im Pokédex nachlesen',
        'review_intro' => 'Öffne einen Eintrag und mache aus jedem Fehler neues Wissen.', 'your_answer' => 'Deine Antwort',
        'open_entry' => 'Eintrag öffnen', 'replay' => 'Erneut spielen', 'change_settings' => 'Einstellungen ändern',
        'perfect_review' => 'Keine Fehler zum Nachlesen. Gut gemacht!', 'noscript' => 'JavaScript muss für das Quiz aktiviert sein.',
    ],
    'it' => [
        'page_title' => 'Quiz Pokémon | CoolPokemonGames',
        'pokedex' => 'Pokédex', 'quizzes' => 'Quiz', 'eyebrow' => 'Centro di allenamento dei cacciatori Pokémon',
        'hero_title' => 'Osserva, ascolta e cattura la risposta giusta!',
        'hero_intro' => 'Indossa il cappello da Allenatore: i Pokémon si nascondono tra immagini, versi e tipi. Trovali tutti!',
        'hero_badge_pokemon' => 'Pokémon da scoprire', 'hero_badge_challenges' => 'sfide diverse', 'hero_badge_types' => 'tipi da dominare',
        'setup_kicker' => 'La tua prossima missione', 'setup_title' => 'Prepara la caccia',
        'setup_intro' => 'Scegli sfida, territorio e durata della missione. I Pokémon cambiano a ogni partita!',
        'mode_label' => 'Scegli la tua sfida', 'mode_mixed' => 'Un po’ di tutto', 'mode_mixed_desc' => 'Immagini, versi e tipi mescolati a caso.',
        'mode_image' => 'Chi è questo Pokémon?', 'mode_image_desc' => 'Riconosci la sagoma e l’illustrazione.',
        'mode_cry' => 'Poké Blindtest', 'mode_cry_desc' => 'Di chi è questo verso?',
        'mode_type' => 'Maestro dei tipi', 'mode_type_desc' => 'Trova la combinazione esatta di tipi.',
        'generation_label' => 'Scegli il territorio di caccia', 'all_generations' => 'Tutte le generazioni', 'generation' => 'Generazione',
        'length_label' => 'Scegli la durata della missione', 'questions' => 'domande', 'start' => 'Inizia la caccia!',
        'best_score' => 'Il tuo trofeo migliore', 'no_score' => 'Stabilisci il primo record!',
        'loading' => 'I Pokémon si nascondono…', 'load_error' => 'Impossibile caricare i dati del Pokédex. Aggiorna la pagina e riprova.',
        'question_progress' => 'Domanda', 'score' => 'Punti', 'image_question' => 'Qual è questo Pokémon?',
        'cry_question' => 'A quale Pokémon appartiene questo verso?', 'type_question' => 'Quali sono i tipi di questo Pokémon?',
        'play_cry' => 'Ascolta il verso', 'replay_cry' => 'Riascolta il verso', 'audio_error' => 'Impossibile riprodurre il verso. Puoi riprovare.',
        'correct' => 'Risposta corretta!', 'incorrect' => 'Non proprio.', 'correct_answer' => 'La risposta corretta era:',
        'next' => 'Continua la caccia', 'see_results' => 'Vedi il trofeo', 'result_kicker' => 'Rapporto missione', 'result_title' => 'Missione compiuta!',
        'result_perfect' => 'Incredibile: nessun errore! Il tuo Pokédex mentale è formidabile.',
        'result_great' => 'Ottima partita! Manca davvero poco alla perfezione.', 'result_good' => 'Buon inizio. Rivedi gli errori e riprova.',
        'result_retry' => 'Ogni Allenatore inizia da qualche parte. Ripassa e riparti all’avventura!',
        'accuracy' => 'Precisione', 'new_best' => 'Nuovo record!', 'review_title' => 'Da rivedere nel Pokédex',
        'review_intro' => 'Apri una scheda per trasformare ogni errore in conoscenza.', 'your_answer' => 'La tua risposta',
        'open_entry' => 'Apri scheda', 'replay' => 'Gioca di nuovo', 'change_settings' => 'Cambia impostazioni',
        'perfect_review' => 'Nessun errore da rivedere. Complimenti!', 'noscript' => 'JavaScript deve essere attivo per giocare.',
    ],
    'ja' => [
        'page_title' => 'ポケモンクイズ | CoolPokemonGames',
        'pokedex' => 'ポケモン図鑑', 'quizzes' => 'クイズ', 'eyebrow' => 'ポケモンハンター訓練センター',
        'hero_title' => '見て、聞いて、正解をゲットしよう！', 'hero_intro' => 'トレーナーになって、画像・鳴き声・タイプに隠れたポケモンを見つけよう！',
        'hero_badge_pokemon' => '見つけるポケモン', 'hero_badge_challenges' => '種類のチャレンジ', 'hero_badge_types' => 'マスターするタイプ',
        'setup_kicker' => '次のミッション', 'setup_title' => '冒険の準備', 'setup_intro' => 'チャレンジ、エリア、ミッションの長さを選ぼう。ポケモンは毎回変わるよ！',
        'mode_label' => 'チャレンジを選ぶ', 'mode_mixed' => 'いろいろミックス', 'mode_mixed_desc' => '画像、鳴き声、タイプをランダムに出題。',
        'mode_image' => 'このポケモンはだれ？', 'mode_image_desc' => 'シルエットとイラストから当てよう。',
        'mode_cry' => 'Poké Blindtest', 'mode_cry_desc' => 'この鳴き声はだれ？',
        'mode_type' => 'タイプマスター', 'mode_type_desc' => '正しいタイプの組み合わせを当てよう。',
        'generation_label' => 'エリアを選ぶ', 'all_generations' => 'すべての世代', 'generation' => '第',
        'length_label' => 'ミッションの長さを選ぶ', 'questions' => '問', 'start' => '冒険スタート！',
        'best_score' => '最高のトロフィー', 'no_score' => '最初の記録を作ろう！',
        'loading' => 'ポケモンが隠れているよ…', 'load_error' => '図鑑データを読み込めませんでした。ページを更新してください。',
        'question_progress' => '問題', 'score' => 'スコア', 'image_question' => 'このポケモンはだれ？',
        'cry_question' => 'この鳴き声はどのポケモン？', 'type_question' => 'このポケモンのタイプは？',
        'play_cry' => '鳴き声を聞く', 'replay_cry' => 'もう一度聞く', 'audio_error' => '鳴き声を再生できませんでした。もう一度試してください。',
        'correct' => '正解！', 'incorrect' => 'おしい！', 'correct_answer' => '正解：', 'next' => '次の問題',
        'see_results' => 'トロフィーを見る', 'result_kicker' => 'ミッション結果', 'result_title' => 'ミッション完了！', 'result_perfect' => 'すごい、全問正解！完璧なポケモン知識です。',
        'result_great' => 'すばらしい結果！全問正解まであと少しです。', 'result_good' => '良いスタートです。間違いを復習してもう一度挑戦しよう。',
        'result_retry' => 'どんなトレーナーにも最初があります。復習して再挑戦しよう！',
        'accuracy' => '正答率', 'new_best' => '新記録！', 'review_title' => '図鑑で復習',
        'review_intro' => '図鑑を開いて間違いを新しい知識に変えよう。', 'your_answer' => 'あなたの回答',
        'open_entry' => '図鑑を見る', 'replay' => '同じ設定でもう一度', 'change_settings' => '設定を変える',
        'perfect_review' => '復習する間違いはありません。おめでとう！', 'noscript' => 'クイズにはJavaScriptが必要です。',
    ],
];

$type_names = [
    'normal' => ['fr' => 'Normal', 'en' => 'Normal', 'es' => 'Normal', 'de' => 'Normal', 'it' => 'Normale', 'ja' => 'ノーマル'],
    'fire' => ['fr' => 'Feu', 'en' => 'Fire', 'es' => 'Fuego', 'de' => 'Feuer', 'it' => 'Fuoco', 'ja' => 'ほのお'],
    'water' => ['fr' => 'Eau', 'en' => 'Water', 'es' => 'Agua', 'de' => 'Wasser', 'it' => 'Acqua', 'ja' => 'みず'],
    'grass' => ['fr' => 'Plante', 'en' => 'Grass', 'es' => 'Planta', 'de' => 'Pflanze', 'it' => 'Erba', 'ja' => 'くさ'],
    'electric' => ['fr' => 'Électrik', 'en' => 'Electric', 'es' => 'Eléctrico', 'de' => 'Elektro', 'it' => 'Elettro', 'ja' => 'でんき'],
    'ice' => ['fr' => 'Glace', 'en' => 'Ice', 'es' => 'Hielo', 'de' => 'Eis', 'it' => 'Ghiaccio', 'ja' => 'こおり'],
    'fighting' => ['fr' => 'Combat', 'en' => 'Fighting', 'es' => 'Lucha', 'de' => 'Kampf', 'it' => 'Lotta', 'ja' => 'かくとう'],
    'poison' => ['fr' => 'Poison', 'en' => 'Poison', 'es' => 'Veneno', 'de' => 'Gift', 'it' => 'Veleno', 'ja' => 'どく'],
    'ground' => ['fr' => 'Sol', 'en' => 'Ground', 'es' => 'Tierra', 'de' => 'Boden', 'it' => 'Terra', 'ja' => 'じめん'],
    'flying' => ['fr' => 'Vol', 'en' => 'Flying', 'es' => 'Volador', 'de' => 'Flug', 'it' => 'Volante', 'ja' => 'ひこう'],
    'psychic' => ['fr' => 'Psy', 'en' => 'Psychic', 'es' => 'Psíquico', 'de' => 'Psycho', 'it' => 'Psico', 'ja' => 'エスパー'],
    'bug' => ['fr' => 'Insecte', 'en' => 'Bug', 'es' => 'Bicho', 'de' => 'Käfer', 'it' => 'Coleottero', 'ja' => 'むし'],
    'rock' => ['fr' => 'Roche', 'en' => 'Rock', 'es' => 'Roca', 'de' => 'Gestein', 'it' => 'Roccia', 'ja' => 'いわ'],
    'ghost' => ['fr' => 'Spectre', 'en' => 'Ghost', 'es' => 'Fantasma', 'de' => 'Geist', 'it' => 'Spettro', 'ja' => 'ゴースト'],
    'dragon' => ['fr' => 'Dragon', 'en' => 'Dragon', 'es' => 'Dragón', 'de' => 'Drache', 'it' => 'Drago', 'ja' => 'ドラゴン'],
    'steel' => ['fr' => 'Acier', 'en' => 'Steel', 'es' => 'Acero', 'de' => 'Stahl', 'it' => 'Acciaio', 'ja' => 'はがね'],
    'dark' => ['fr' => 'Ténèbres', 'en' => 'Dark', 'es' => 'Siniestro', 'de' => 'Unlicht', 'it' => 'Buio', 'ja' => 'あく'],
    'fairy' => ['fr' => 'Fée', 'en' => 'Fairy', 'es' => 'Hada', 'de' => 'Fee', 'it' => 'Folletto', 'ja' => 'フェアリー'],
];

// IDs stables des ressources Type de PokéAPI. Ils permettent d'utiliser les
// pictogrammes compacts officiels de Pokémon Scarlet/Violet exposés par l'API.
$type_icon_ids = [
    'normal' => 1, 'fighting' => 2, 'flying' => 3, 'poison' => 4,
    'ground' => 5, 'rock' => 6, 'bug' => 7, 'ghost' => 8,
    'steel' => 9, 'fire' => 10, 'water' => 11, 'grass' => 12,
    'electric' => 13, 'psychic' => 14, 'ice' => 15, 'dragon' => 16,
    'dark' => 17, 'fairy' => 18,
];

$copy = $translations[$lang] ?? $translations['en'];

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="<?php echo h($lang); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo h($copy['hero_intro']); ?>">
    <title><?php echo h($copy['page_title']); ?></title>
    <link rel="stylesheet" href="/assets/quiz.css">
    <script>
        window.CPG_QUIZ = <?php echo json_encode([
            'lang' => $lang,
            'copy' => $copy,
            'typeNames' => array_map(function ($names) use ($lang) {
                return $names[$lang] ?? $names['en'];
            }, $type_names),
            'typeIconIds' => $type_icon_ids,
            'typeIconBaseUrl' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/types/generation-ix/scarlet-violet/small/',
            'dataUrl' => '/pokedex.json',
            'pokedexBaseUrl' => '/' . $lang,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="/assets/quiz.js" defer></script>
</head>
<body>
    <header class="site-header">
        <div class="header-content">
            <a href="/<?php echo h($lang); ?>" class="brand" aria-label="CoolPokemonGames">
                <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/items/poke-ball.png" alt="" width="40" height="40">
                <span>CoolPokemonGames</span>
            </a>
            <div class="header-actions">
                <nav class="site-nav" aria-label="Navigation principale">
                    <a href="/<?php echo h($lang); ?>"><?php echo h($copy['pokedex']); ?></a>
                    <a href="/<?php echo h($lang); ?>/quiz" class="active" aria-current="page"><?php echo h($copy['quizzes']); ?></a>
                </nav>
                <details class="language-menu">
                    <summary aria-label="Language">
                        <span class="flag flag-<?php echo h($lang); ?>" aria-hidden="true"></span>
                        <span><?php echo h(strtoupper($lang)); ?></span>
                    </summary>
                    <nav class="language-menu-list" aria-label="Available languages">
                        <?php foreach ($allowed_langs as $language): ?>
                            <a href="/<?php echo h($language); ?>/quiz"<?php echo $lang === $language ? ' class="current" aria-current="page"' : ''; ?>>
                                <span class="flag flag-<?php echo h($language); ?>" aria-hidden="true"></span>
                                <span><?php echo h($lang_names[$language]); ?></span>
                                <span class="language-menu-code"><?php echo h(strtoupper($language)); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </details>
            </div>
        </div>
    </header>

    <main>
        <section class="quiz-hero">
            <div class="hero-orb hero-orb-one"></div>
            <div class="hero-orb hero-orb-two"></div>
            <div class="hero-inner">
                <p class="eyebrow"><?php echo h($copy['eyebrow']); ?></p>
                <h1><?php echo h($copy['hero_title']); ?></h1>
                <p class="hero-intro"><?php echo h($copy['hero_intro']); ?></p>
                <div class="hero-badges">
                    <span><b>1 025</b><?php echo h($copy['hero_badge_pokemon']); ?></span>
                    <span><b>3</b><?php echo h($copy['hero_badge_challenges']); ?></span>
                    <span><b>18</b><?php echo h($copy['hero_badge_types']); ?></span>
                </div>
            </div>
        </section>

        <div class="quiz-shell">
            <noscript><p class="notice notice-error"><?php echo h($copy['noscript']); ?></p></noscript>

            <section id="setupPanel" class="panel setup-panel" aria-labelledby="setupTitle">
                <div class="section-heading">
                    <div>
                        <p class="section-kicker"><?php echo h($copy['setup_kicker']); ?></p>
                        <h2 id="setupTitle"><?php echo h($copy['setup_title']); ?></h2>
                    </div>
                    <p><?php echo h($copy['setup_intro']); ?></p>
                </div>

                <form id="quizSetup">
                    <fieldset>
                        <legend><span class="step-number">1</span><?php echo h($copy['mode_label']); ?></legend>
                        <div class="mode-grid">
                            <label class="mode-card">
                                <input type="radio" name="mode" value="mixed" checked>
                                <span class="mode-card-body">
                                    <span class="mode-icon mode-icon-mixed" aria-hidden="true">✦</span>
                                    <strong><?php echo h($copy['mode_mixed']); ?></strong>
                                    <small><?php echo h($copy['mode_mixed_desc']); ?></small>
                                    <span class="mode-check" aria-hidden="true">✓</span>
                                </span>
                            </label>
                            <label class="mode-card">
                                <input type="radio" name="mode" value="image">
                                <span class="mode-card-body">
                                    <span class="mode-icon mode-icon-image" aria-hidden="true">?</span>
                                    <strong><?php echo h($copy['mode_image']); ?></strong>
                                    <small><?php echo h($copy['mode_image_desc']); ?></small>
                                    <span class="mode-check" aria-hidden="true">✓</span>
                                </span>
                            </label>
                            <label class="mode-card">
                                <input type="radio" name="mode" value="cry">
                                <span class="mode-card-body">
                                    <span class="mode-icon mode-icon-cry" aria-hidden="true">♫</span>
                                    <strong><?php echo h($copy['mode_cry']); ?></strong>
                                    <small><?php echo h($copy['mode_cry_desc']); ?></small>
                                    <span class="mode-check" aria-hidden="true">✓</span>
                                </span>
                            </label>
                            <label class="mode-card">
                                <input type="radio" name="mode" value="type">
                                <span class="mode-card-body">
                                    <span class="mode-icon mode-icon-type" aria-hidden="true">◆</span>
                                    <strong><?php echo h($copy['mode_type']); ?></strong>
                                    <small><?php echo h($copy['mode_type_desc']); ?></small>
                                    <span class="mode-check" aria-hidden="true">✓</span>
                                </span>
                            </label>
                        </div>
                    </fieldset>

                    <div class="setup-row">
                        <fieldset class="setup-field">
                            <legend><span class="step-number">2</span><?php echo h($copy['generation_label']); ?></legend>
                            <label class="select-wrap">
                                <select id="generationSelect" name="generation">
                                    <option value="all"><?php echo h($copy['all_generations']); ?></option>
                                    <?php for ($generation = 1; $generation <= 9; $generation++): ?>
                                        <option value="<?php echo $generation; ?>">
                                            <?php echo h($copy['generation']); ?> <?php echo $generation; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </label>
                        </fieldset>

                        <fieldset class="setup-field">
                            <legend><span class="step-number">3</span><?php echo h($copy['length_label']); ?></legend>
                            <div class="length-options">
                                <?php foreach ([5, 10, 20] as $count): ?>
                                    <label>
                                        <input type="radio" name="count" value="<?php echo $count; ?>" <?php echo $count === 10 ? 'checked' : ''; ?>>
                                        <span><strong><?php echo $count; ?></strong> <?php echo h($copy['questions']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                    </div>

                    <div class="setup-footer">
                        <div class="best-score-card">
                            <span class="trophy" aria-hidden="true">★</span>
                            <span><small><?php echo h($copy['best_score']); ?></small><strong id="bestScoreText"><?php echo h($copy['no_score']); ?></strong></span>
                        </div>
                        <button type="submit" id="startButton" class="primary-button">
                            <span aria-hidden="true">◉</span><span><?php echo h($copy['start']); ?></span><span aria-hidden="true">→</span>
                        </button>
                    </div>
                </form>
            </section>

            <section id="loadingPanel" class="panel status-panel" hidden aria-live="polite">
                <div class="pokeball-loader" aria-hidden="true"></div>
                <p><?php echo h($copy['loading']); ?></p>
            </section>

            <section id="errorPanel" class="panel status-panel status-error" hidden role="alert">
                <span aria-hidden="true">!</span>
                <p><?php echo h($copy['load_error']); ?></p>
            </section>

            <section id="gamePanel" class="panel game-panel" hidden aria-labelledby="questionTitle">
                <div class="game-status">
                    <div class="progress-copy">
                        <span id="questionCounter"><?php echo h($copy['question_progress']); ?> 1 / 10</span>
                        <strong id="liveScore"><?php echo h($copy['score']); ?> : 0</strong>
                    </div>
                    <div class="progress-track" aria-hidden="true"><span id="progressBar"></span></div>
                </div>

                <div class="question-layout">
                    <div id="mediaStage" class="media-stage">
                        <span id="pokemonNumber" class="pokemon-number"></span>
                        <img id="quizImage" src="" alt="" width="320" height="320">
                        <div id="cryStage" class="cry-stage" hidden>
                            <div class="sound-rings" aria-hidden="true"><span></span><span></span><span></span></div>
                            <button type="button" id="playCryButton" class="sound-button">
                                <span aria-hidden="true">▶</span><strong><?php echo h($copy['play_cry']); ?></strong>
                            </button>
                            <audio id="cryAudio" preload="auto"></audio>
                            <p id="audioError" class="audio-error" hidden><?php echo h($copy['audio_error']); ?></p>
                        </div>
                        <div id="pokemonIdentity" class="pokemon-identity" hidden>
                            <strong id="pokemonIdentityName"></strong>
                            <div id="revealedTypes" class="revealed-types" hidden></div>
                        </div>
                    </div>

                    <div class="question-content">
                        <p id="modeKicker" class="section-kicker"></p>
                        <h2 id="questionTitle"></h2>
                        <div id="choices" class="choices" role="group" aria-labelledby="questionTitle"></div>
                        <div id="feedback" class="feedback" hidden aria-live="polite">
                            <div>
                                <strong id="feedbackTitle"></strong>
                                <p id="feedbackAnswer"></p>
                            </div>
                            <button type="button" id="nextButton" class="primary-button compact"></button>
                        </div>
                    </div>
                </div>
            </section>

            <section id="resultPanel" class="panel result-panel" hidden aria-labelledby="resultTitle">
                <div class="result-summary">
                    <div id="scoreRing" class="score-ring">
                        <span id="finalScore">0/10</span>
                        <small id="accuracyText">0%</small>
                    </div>
                    <div class="result-copy">
                        <p class="section-kicker"><?php echo h($copy['result_kicker']); ?></p>
                        <h2 id="resultTitle"><?php echo h($copy['result_title']); ?></h2>
                        <p id="resultMessage"></p>
                        <strong id="newBestBadge" class="new-best" hidden>★ <?php echo h($copy['new_best']); ?></strong>
                    </div>
                </div>

                <div class="review-block">
                    <div class="review-heading">
                        <div>
                            <h3><?php echo h($copy['review_title']); ?></h3>
                            <p><?php echo h($copy['review_intro']); ?></p>
                        </div>
                    </div>
                    <div id="reviewList" class="review-list"></div>
                </div>

                <div class="result-actions">
                    <button type="button" id="replayButton" class="primary-button"><?php echo h($copy['replay']); ?></button>
                    <button type="button" id="settingsButton" class="secondary-button"><?php echo h($copy['change_settings']); ?></button>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <span>CoolPokemonGames</span>
        <a href="/<?php echo h($lang); ?>"><?php echo h($copy['pokedex']); ?></a>
    </footer>
</body>
</html>
