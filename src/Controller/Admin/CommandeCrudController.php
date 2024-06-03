<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

class CommandeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commande::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user')->setLabel('Utilisateur'),
            AssociationField::new('articles')->setLabel('Articles')->hideOnIndex(),
            MoneyField::new('total')
                ->setCurrency('EUR')
                ->setLabel('Total (€)')
                ->formatValue(function ($value) {
                    return $value;
                }),
            DateTimeField::new('createdAt')->setLabel('Date de création'),
        ];
    }
}
