<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Retrieve the list of tasks in relation to a parameter
     *
     * @param  string $is_done ended for completed tasks, progress for ongoing tasks
     * @return array task list
     */
    public function findTaskList(string $is_done): array
    {
        $entityManager = $this->getEntityManager();
        $result = '';

        switch ($is_done) {
            case 'ended':
                $result = false;

                break;
            case 'progress':
                $result = true;

                break;
            default:
                return $this->findAll();

                break;
        }

        $query = $entityManager->createQuery(
            'SELECT t
            FROM App\Entity\Task t
            WHERE t.isDone = :isDone
            ORDER BY t.createdAt ASC'
        )->setParameter('isDone', $result);

        return $query->getResult();
    }
}
