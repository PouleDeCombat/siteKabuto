<?php
namespace App\Controller;

use Dompdf\Dompdf;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PdfGeneratorController extends AbstractController
{
    private $usersRepository;

    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

    #[Route('/pdf/generator/{id}', name: 'app_pdf_generator')]
    public function index($id): Response
    {
        $user = $this->usersRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur n\'a pas été trouvé');
        }

        $data = [
            'user' => $user,
            // autres variables...
        ];

        $html = $this->renderView('pdf_generator/index.html.twig', $data);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Sauvegarder le PDF quelque part si nécessaire, ou l'attacher à un e-mail, etc.

        return new Response(
            $dompdf->stream('resume', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }
}
