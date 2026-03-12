<?php

declare(strict_types=1);

/**
 * Seeder: Blog Categories, Posts, Tags, Comments
 */

return [
    'run' => function (\PDO $pdo): void {
        // Categories
        $pdo->exec("
            INSERT INTO blog_categories (name, slug, description, is_active) VALUES
            ('Santé & Bien-être', 'sante-bien-etre', 'Articles sur la santé générale et le bien-être', TRUE),
            ('Actualités Médicales', 'actualites-medicales', 'Dernières nouvelles du monde médical', TRUE),
            ('Conseils Pharmacie', 'conseils-pharmacie', 'Conseils et astuces pharmaceutiques', TRUE),
            ('Nutrition', 'nutrition', 'Articles sur la nutrition et l''alimentation', TRUE),
            ('Prévention', 'prevention', 'Prévention des maladies et hygiène de vie', TRUE)
            ON DUPLICATE KEY UPDATE slug=slug
        ");

        // Tags
        $tags = ['santé', 'prévention', 'nutrition', 'médicaments', 'bien-être', 'paludisme', 'covid-19', 'vitamines', 'grossesse', 'enfants'];
        $insertTag = $pdo->prepare("INSERT INTO blog_tags (name, slug) VALUES (?, ?) ON DUPLICATE KEY UPDATE slug=slug");
        foreach ($tags as $tag) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $tag));
            $insertTag->execute([$tag, $slug]);
        }

        // Get admin/moderator as authors
        $authorIds = $pdo->query("
            SELECT u.id FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON r.id = ur.role_id AND r.name IN ('admin','moderator')
        ")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($authorIds)) return;

        $catIds = $pdo->query("SELECT id FROM blog_categories")->fetchAll(\PDO::FETCH_COLUMN);
        $tagIds = $pdo->query("SELECT id FROM blog_tags")->fetchAll(\PDO::FETCH_COLUMN);

        // Blog posts
        $posts = [
            [
                'title'   => 'Comment se protéger efficacement du paludisme en RDC',
                'slug'    => 'se-proteger-paludisme-rdc',
                'excerpt' => 'Le paludisme reste la première cause de mortalité en RDC. Découvrez les gestes essentiels pour vous protéger.',
                'content' => '<p>Le paludisme est une maladie parasitaire transmise par les moustiques du genre Anopheles. En République Démocratique du Congo, il constitue un problème de santé publique majeur.</p><h2>Les gestes de prévention</h2><p>1. <strong>Dormir sous une moustiquaire imprégnée</strong> — C\'est la mesure la plus efficace.</p><p>2. <strong>Utiliser des répulsifs</strong> — Appliquez un répulsif à base de DEET sur les zones exposées.</p><p>3. <strong>Consulter rapidement</strong> — En cas de fièvre, consultez dans les 24 heures.</p><h2>Traitement</h2><p>Le traitement recommandé est la combinaison thérapeutique à base d\'artémisinine (ACT), disponible dans toutes les pharmacies AfiaZone.</p>',
                'cat_idx' => 0,
            ],
            [
                'title'   => '10 aliments pour renforcer votre système immunitaire',
                'slug'    => '10-aliments-renforcer-systeme-immunitaire',
                'excerpt' => 'Une alimentation équilibrée est votre meilleur allié contre les infections. Voici les 10 super-aliments à intégrer dans votre quotidien.',
                'content' => '<p>L\'alimentation joue un rôle crucial dans le fonctionnement de notre système immunitaire.</p><h2>Les 10 super-aliments</h2><ol><li><strong>Agrumes</strong> — Riches en vitamine C</li><li><strong>Gingembre</strong> — Anti-inflammatoire naturel</li><li><strong>Ail</strong> — Propriétés antimicrobiennes</li><li><strong>Épinards</strong> — Riches en antioxydants</li><li><strong>Yaourt</strong> — Probiotiques pour la flore intestinale</li><li><strong>Amandes</strong> — Source de vitamine E</li><li><strong>Curcuma</strong> — Puissant anti-inflammatoire</li><li><strong>Thé vert</strong> — Riche en flavonoïdes</li><li><strong>Papaye</strong> — Vitamine C et enzymes digestives</li><li><strong>Patates douces</strong> — Bêta-carotène et fibres</li></ol>',
                'cat_idx' => 3,
            ],
            [
                'title'   => 'Guide complet : Bien utiliser vos médicaments',
                'slug'    => 'guide-complet-bien-utiliser-medicaments',
                'excerpt' => 'Respecter la posologie, vérifier la date de péremption... Suivez notre guide pour une utilisation sûre de vos médicaments.',
                'content' => '<p>Une mauvaise utilisation des médicaments peut être dangereuse. Voici les règles essentielles à respecter.</p><h2>1. Respectez la posologie</h2><p>Ne modifiez jamais les doses prescrites par votre médecin.</p><h2>2. Vérifiez la date de péremption</h2><p>Un médicament périmé peut être inefficace voire dangereux.</p><h2>3. Conservez correctement</h2><p>Température ambiante, à l\'abri de la lumière et de l\'humidité.</p><h2>4. Ne partagez pas vos médicaments</h2><p>Un traitement est personnel et adapté à votre situation.</p>',
                'cat_idx' => 2,
            ],
            [
                'title'   => 'Vaccination COVID-19 : Ce que vous devez savoir',
                'slug'    => 'vaccination-covid-19-ce-que-vous-devez-savoir',
                'excerpt' => 'Tout savoir sur les vaccins disponibles, les effets secondaires et le calendrier de vaccination.',
                'content' => '<p>La vaccination reste le moyen le plus efficace pour se protéger contre les formes graves du COVID-19.</p><h2>Vaccins disponibles</h2><p>Plusieurs vaccins sont disponibles en RDC, notamment Pfizer, AstraZeneca et Johnson & Johnson.</p><h2>Qui doit se faire vacciner ?</h2><p>La vaccination est recommandée pour toute personne de plus de 12 ans, et particulièrement pour les personnes à risque.</p>',
                'cat_idx' => 1,
            ],
            [
                'title'   => 'Grossesse : Les vitamines essentielles à ne pas oublier',
                'slug'    => 'grossesse-vitamines-essentielles',
                'excerpt' => 'Fer, acide folique, vitamine D... Découvrez les suppléments indispensables pendant la grossesse.',
                'content' => '<p>Pendant la grossesse, les besoins nutritionnels augmentent significativement.</p><h2>Acide folique (Vitamine B9)</h2><p>Essentiel pour le développement du système nerveux du fœtus. À prendre dès le désir de grossesse.</p><h2>Fer</h2><p>Prévient l\'anémie, très fréquente pendant la grossesse en RDC.</p><h2>Vitamine D</h2><p>Pour la santé osseuse de la mère et du bébé.</p><h2>Calcium</h2><p>1000mg/jour recommandés pour prévenir la pré-éclampsie.</p><p>Retrouvez tous ces suppléments sur AfiaZone avec livraison à domicile.</p>',
                'cat_idx' => 0,
            ],
            [
                'title'   => 'Les 5 règles d\'or de l\'hygiène des mains',
                'slug'    => '5-regles-or-hygiene-des-mains',
                'excerpt' => 'Le lavage des mains est le geste le plus simple et le plus efficace pour prévenir les infections.',
                'content' => '<p>L\'Organisation Mondiale de la Santé estime que le lavage des mains pourrait réduire de 50% les maladies diarrhéiques.</p><h2>Quand se laver les mains ?</h2><ol><li>Avant de manger</li><li>Après être allé aux toilettes</li><li>Après avoir toussé ou éternué</li><li>Avant de soigner une plaie</li><li>Après avoir touché des surfaces publiques</li></ol><h2>Comment bien se laver les mains ?</h2><p>Eau et savon pendant au moins 20 secondes, en n\'oubliant pas les espaces entre les doigts et sous les ongles.</p>',
                'cat_idx' => 4,
            ],
        ];

        $insertPost = $pdo->prepare("
            INSERT INTO blog_posts (author_id, category_id, title, slug, excerpt, content, status, is_featured, view_count, published_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'published', ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE slug=slug
        ");

        $insertPostTag = $pdo->prepare("
            INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (?, ?)
        ");

        foreach ($posts as $i => $post) {
            $authorId = $authorIds[array_rand($authorIds)];
            $catId = $catIds[$post['cat_idx']] ?? $catIds[0];
            $featured = ($i < 2) ? 1 : 0;
            $views = rand(50, 5000);
            $daysAgo = rand(1, 120);
            $date = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));

            $insertPost->execute([
                $authorId, $catId, $post['title'], $post['slug'],
                $post['excerpt'], $post['content'],
                $featured, $views, $date, $date
            ]);

            $pid = $pdo->query("SELECT id FROM blog_posts WHERE slug = " . $pdo->quote($post['slug']))->fetchColumn();
            if (!$pid) continue;

            // Assign 2-3 random tags
            $postTags = array_slice($tagIds, 0, rand(2, 4));
            shuffle($postTags);
            foreach (array_slice($postTags, 0, rand(2, 3)) as $tid) {
                $insertPostTag->execute([$pid, $tid]);
            }
        }

        // Comments
        $customerIds = $pdo->query("
            SELECT u.id FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON r.id = ur.role_id AND r.name = 'customer'
        ")->fetchAll(\PDO::FETCH_COLUMN);

        $postIds = $pdo->query("SELECT id FROM blog_posts")->fetchAll(\PDO::FETCH_COLUMN);

        $commentTexts = [
            'Merci beaucoup pour cet article très informatif !',
            'Très utile, je partage avec ma famille.',
            'Est-ce que ces conseils s\'appliquent aussi aux enfants ?',
            'Article très bien écrit, j\'ai appris beaucoup de choses.',
            'Pouvez-vous faire un article sur le diabète ?',
            'Information précieuse, surtout pour nous en RDC.',
            'Je confirme, le paracétamol de AfiaZone est de bonne qualité.',
            'Merci docteur pour ces conseils pratiques.',
        ];

        $insertComment = $pdo->prepare("
            INSERT INTO blog_comments (post_id, user_id, content, status, created_at)
            VALUES (?, ?, ?, 'approved', NOW() - INTERVAL ? DAY)
        ");

        if (!empty($customerIds)) {
            foreach ($postIds as $postId) {
                $numComments = rand(1, 4);
                for ($c = 0; $c < $numComments; $c++) {
                    $uid = $customerIds[array_rand($customerIds)];
                    $text = $commentTexts[array_rand($commentTexts)];
                    $insertComment->execute([$postId, $uid, $text, rand(1, 60)]);
                }
            }
        }
    },
];
