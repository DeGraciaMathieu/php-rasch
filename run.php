<?php

require __DIR__ . '/vendor/autoload.php';

$responses = [
    [1, 0, 1, 1],
    [0, 0, 1, 0],
    [1, 1, 1, 1],
];

$nbPersons = count($responses);
$nbResponses = count($responses[0]);

$theta = array_fill(0, $nbPersons, 0.0); // compétences
$b = array_fill(0, $nbResponses, 0.0);   // difficultés

/**
 * Met à jour les compétences (theta) pour toutes les personnes
 */
function updateThetas(&$theta, $responses, $b, $learningRate, $nbPersons, $nbResponses) {
    for ($i = 0; $i < $nbPersons; $i++) {
        $grad = 0.0;
        for ($j = 0; $j < $nbResponses; $j++) {
            $p = raschProbability($theta[$i], $b[$j]);
            $grad += ($responses[$i][$j] - $p);
        }
        $theta[$i] += $learningRate * $grad;
    }
}

/**
 * Met à jour les difficultés (b) pour tous les items
 */
function updateDifficulties(&$b, $responses, $theta, $learningRate, $nbPersons, $nbResponses) {
    for ($j = 0; $j < $nbResponses; $j++) {
        $grad = 0.0;
        for ($i = 0; $i < $nbPersons; $i++) {
            $p = raschProbability($theta[$i], $b[$j]);
            $grad += ($p - $responses[$i][$j]);
        }
        $b[$j] += $learningRate * $grad;
    }
}

/**
 * Formule du modèle Rasch
 * Calcule la probabilité qu’une personne réussisse un item
 * - θ (theta) = le niveau de compétence de la personne,
 * - b = la difficulté de l’item.
 */
function raschProbability(float $theta, float $b): float
{
    return exp($theta - $b) / (1 + exp($theta - $b));
}

// nombre d'itérations pour obtenir des données fiables
$iterations = 500;
// taille du "pas" effectué à chaque itération
$learningRate = 0.01;

/**
 * On itère 500 fois pour obtenir des données fiables, chaque itération permet d'affiner les données
 */
for ($iter = 0; $iter < $iterations; $iter++) {
    
    // Mise à jour des compétences (theta)
    updateThetas($theta, $responses, $b, $learningRate, $nbPersons, $nbResponses);

    // Mise à jour des difficultés (b)
    updateDifficulties($b, $responses, $theta, $learningRate, $nbPersons, $nbResponses);
}

echo "Compétences estimées (theta) \n";
foreach ($theta as $i => $val) {
    echo "Personne " . ($i+1) . " : " . round($val, 3) . "\n";
}

echo "\nDifficultés estimées (b)\n";
foreach ($b as $j => $val) {
    echo "Item " . ($j+1) . " : " . round($val, 3) . "\n";
}

/**
 * Trouve l’item le plus adapté à une personne en fonction de son theta
 * => celui dont la difficulté b est la plus proche de θ
 */
function recommendItemForUser(float $theta, array $items): int
{
    $closestItem = 0;
    $minDiff = INF;

    foreach ($items as $id => $difficulty) {

        // La difficulty est b
        $diff = abs($theta - $difficulty);

        if ($diff < $minDiff) {
            $minDiff = $diff;
            $closestItem = $id;
        }
    }

    return $closestItem; // index de l’item avec la difficulty la plus proche de theta
}

echo "\nRecommandations d’items pour chaque personne :\n";
foreach ($theta as $i => $val) {
    $item = recommendItemForUser($val, $b);
    echo "Personne " . ($i + 1) . " -> Item " . ($item + 1) . "\n";
}