<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\Walk;
use App\Form\Type\WalkPrologueType;
use App\Form\Type\WalkType;
use App\Repository\SystemicQuestionRepository;
use App\Repository\UserRepository;
use App\Repository\WalkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

class WalkController
{
    /** @var WalkRepository */
    private $walkRepository;
    /** @var RouterInterface */
    private $router;
    /** @var UserRepository */
    private $userRepository;
    /** @var SystemicQuestionRepository */
    private $systemicQuestionRepository;
    /** @var FormFactoryInterface */
    private $formFactory;

    /**
     * @param WalkRepository             $walkRepository
     * @param RouterInterface            $router
     * @param FormFactoryInterface       $formFactory
     * @param UserRepository             $userRepository
     * @param SystemicQuestionRepository $systemicQuestionRepository
     */
    public function __construct(
        WalkRepository $walkRepository,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        UserRepository $userRepository,
        SystemicQuestionRepository $systemicQuestionRepository
    ) {
        $this->walkRepository = $walkRepository;
        $this->router = $router;
        $this->userRepository = $userRepository;
        $this->systemicQuestionRepository = $systemicQuestionRepository;
        $this->formFactory = $formFactory;
    }

    /**
     * @param User    $user
     * @param Request $request
     *
     * @return array
     *
     * @Route("walks", name="walk_home_screen")
     *
     * @Template(template="walk/homeScreen.html.twig")
     */
    public function homeScreenAction(User $user, Request $request): array
    {
        $order = $request->query->get('order', 'startTime');
        $sort = $request->query->get('sort', 'desc');

        $walks = $this->walkRepository->findAllOrderBy('walk.'.$order, $sort);
        $unfinishedWalks = $this->walkRepository->findAllUnfinishedByUser($user);
        $teams = $user->getTeams();

        return [
            'unfinishedWalks' => $unfinishedWalks,
            'walks' => $walks,
            'teams' => $teams,
            'order' => $order,
            'sort' => $sort,
        ];
    }

    /**
     * @param Walk $walk
     *
     * @Route("walk/{walkId}", name="walk_show")
     *
     * @Template(template="walk/show.html.twig")
     *
     * @return array
     */
    public function showAction(Walk $walk): array
    {
        return [
            'walk' => $walk,
        ];
    }

    /**
     * @param Walk    $walk
     * @param Request $request
     *
     * @Route("createwalk/{walkId}", name="walk_create_form")
     *
     * @Template(template="walk/createWalkForm.html.twig")
     *
     * @return array
     */
    public function createWalkFormAction(Walk $walk, Request $request): array
    {
        $form = $this->formFactory->create(
            WalkType::class,
            $walk,
            [
                'action' => $this->router->generate('walk_create', ['walkId' => $walk->getId()]),
            ]
        );
        $form->handleRequest($request);

        return [
            'walk' => $walk,
            'form' => $form->createView(),
            'wayPoints' => $walk->getWayPoints(),
        ];
    }

    /**
     * @param Team    $team
     * @param Request $request
     *
     * @Route("/startWalkPrologueWithTeam/{teamId}", name="start_walk_with_walk_prologue")
     *
     * @Template(template="walk/createWalkPrologueForm.html.twig")
     *
     * @return array
     */
    public function createWalkPrologueFormAction(Team $team, Request $request): array
    {
        $systemicQuestion = $this->systemicQuestionRepository->getRandom();
        $walk = Walk::prologue($team, $systemicQuestion);

        $this->walkRepository->save($walk);
        foreach ($team->getUsers() as $user) {
            $user->setWalks(new ArrayCollection([$walk]));
            $this->userRepository->save($user);
        }

        $form = $this->formFactory->create(
            WalkPrologueType::class,
            $walk,
            [
                'action' => $this->router->generate('walk_start', ['walkId' => $walk->getId()]),
            ]
        );
        $form->handleRequest($request);

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @param FlashBagInterface $flash
     * @param Walk              $walk
     * @param Request           $request
     *
     * @Route("/walkstarted/{walkId}", name="walk_start")
     *
     * @Template(template="walk/createWalkPrologue.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function createWalkPrologueAction(FlashBagInterface $flash, Walk $walk, Request $request)
    {
        $form = $this->formFactory->create(WalkPrologueType::class, $walk);
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return [
                'form' => $form->createView(),
            ];
        }

        $walk = $form->getData();
        $this->walkRepository->update($walk);
        $flash->add(
            'notice',
            'Runde wurde erfolgreich gestartet.'
        );

        $url = $this->router->generate('update_walk_with_way_point', ['walkId' => $walk->getId()]);
        Assert::notNull($url);

        return new RedirectResponse($url);
    }

    /**
     * @param FlashBagInterface $flash
     * @param Walk              $walk
     * @param Request           $request
     *
     * @Route("walkcreated", name="walk_create")
     *
     * @Template(template="walk/createWalk.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function createWalkAction(FlashBagInterface $flash, Walk $walk, Request $request)
    {
        $form = $this->formFactory->create(WalkType::class, $walk);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return [
                'walk' => $walk,
                'wayPoints' => $walk->getWayPoints(),
                'form' => $form->createView(),
            ];
        }

        $walk = $form->getData();
        $this->walkRepository->update($walk);

        $flash->add(
            'notice',
            'Runde wurde erfolgreich erstellt.'
        );

        $url = $this->router->generate('walk_home_screen');
        Assert::notNull($url);

        return new RedirectResponse($url);
    }

    /**
     * @Route("walkexport", name="walk_export")
     *
     * @return StreamedResponse
     */
    public function exportAction(): StreamedResponse
    {
        // get the service container to pass to the closure
        $walkRepository = $this->walkRepository;

        $response = new StreamedResponse(
            static function () use ($walkRepository): void {
                // The getExportQuery method returns a query that is used to retrieve
                // all the objects (lines of your csv file) you need. The iterate method
                // is used to limit the memory consumption
                $results = $walkRepository->getFindAllQuery()->iterate();
                $handle = \fopen('php://output', 'r+');

                $header = [
                    'Id',
                    'Name',
                    'Beginn',
                    'Ende',
                    'Reflexion',
                    'Bewertung',
                    'systemische Frage',
                    'systemische Antwort',
                    'Erkenntnisse, Überlegungen, Zielsetzungen',
                    'Termine, Besorgungen, Verabredungen',
                    'Wiedervorlage Dienstberatung',
                    'Wetter',
                    'Ferien',
                    'Tageskonzept',
                    'angetroffene Männer',
                    'angetroffene Frauen',
                    'Teamname',
                    //                    'TeamMitglieder',
                    //                    'Gäste',
                ];

                Assert::resource($handle);
                \fputcsv($handle, $header);

                while (false !== ($row = $results->next())) {
                    // add a line in the csv file. You need to implement a toArray() method
                    // to transform your object into an array
                    //                dump($row[0]->toArray());
                    \fputcsv($handle, $row[0]->toArray());
                    // used to limit the memory consumption
                    //                $em->detach($row[0]);
                }

                \fclose($handle);
            }
        );

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

        return $response;
    }
}
