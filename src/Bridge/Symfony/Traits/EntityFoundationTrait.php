<?php
namespace ObjectFoundation\Bridge\Symfony\Traits;

/**
 * Мета-трейт: собранный набор по умолчанию.
 * Можно заменить на собственную комбинацию атомов.
 */
trait EntityFoundationTrait
{
    use IdentityTrait;
    use AuditTrait;
    use PublicationTrait;
    use SoftDeleteTrait;
    use VersionableTrait;
    use LockableTrait;
    use ConfigurableTrait;
    use RestrictableTrait;
    use WorkFlowTrait;
    use CreatorTrait;
    use CodedTrait;
    use TokenizedTrait;
}
