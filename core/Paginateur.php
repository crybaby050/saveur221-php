<?php

declare(strict_types=1);

namespace Core;

/*
 * Pagination en mémoire, appliquée après récupération complète d'une
 * liste par le repository. Choix cohérent avec le reste du projet (voir
 * CommandeService::calculerStatistiques(), qui recharge l'intégralité
 * des commandes pour la même raison) : suffisant pour le volume de
 * données attendu ici, à revoir avec du LIMIT/OFFSET SQL si le volume
 * venait à grossir significativement.
 */
final class Paginateur
{
    private const PAR_PAGE_DEFAUT = 12;

    public readonly int $page;
    public readonly int $parPage;
    public readonly int $total;
    public readonly int $totalPages;
    public readonly array $elements;

    public function __construct(array $tousLesElements, int $page, int $parPage = self::PAR_PAGE_DEFAUT)
    {
        $this->total = count($tousLesElements);
        $this->parPage = max(1, $parPage);
        $this->totalPages = max(1, (int) ceil($this->total / $this->parPage));
        $this->page = max(1, min($page, $this->totalPages));

        $debut = ($this->page - 1) * $this->parPage;
        $this->elements = array_slice($tousLesElements, $debut, $this->parPage);
    }

    public function aPlusieursPages(): bool
    {
        return $this->totalPages > 1;
    }
}