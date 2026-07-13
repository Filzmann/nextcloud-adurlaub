<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Controller;

use DateTimeImmutable;
use OCA\AdUrlaub\Exception\VacationConflictException;
use OCA\AdUrlaub\AppInfo\Application;
use OCA\AdUrlaub\Service\VacationAccessService;
use OCA\AdUrlaub\Service\VacationService;
use OCA\AdUrlaub\Service\VacationSettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

final class ApiController extends Controller {
    public function __construct(IRequest $request, private VacationAccessService $access, private VacationService $vacations, private VacationSettingsService $settingsService, private LoggerInterface $logger) { parent::__construct(Application::APP_ID, $request); }
    #[NoAdminRequired, NoCSRFRequired]
    public function week(string $start): JSONResponse { if (!$this->access->canView()) return $this->denied(); try { $data = $this->vacations->week(new DateTimeImmutable($start), $this->access->visibleEmployees()); $data['vacations'] = array_map(fn(array $vacation): array => $vacation + ['canManage' => $this->access->canManageStatus($vacation['employeeUid'], $vacation['status'])], $data['vacations']); return new JSONResponse($data); } catch (\Throwable $error) { $this->logger->error('Urlaubswoche konnte nicht geladen werden.', ['exception' => $error]); return new JSONResponse(['error' => 'Die Urlaubswoche konnte nicht geladen werden.'], Http::STATUS_BAD_REQUEST); } }
    #[NoAdminRequired]
    public function create(string $employeeUid, string $startDate, string $endDate, string $status, string $note = ''): JSONResponse { return $this->save(null, compact('employeeUid','startDate','endDate','status','note')); }
    #[NoAdminRequired]
    public function update(int $id, string $employeeUid, string $startDate, string $endDate, string $status, string $note = ''): JSONResponse { try { $existing = $this->vacations->existing($id); } catch (\Throwable) { return new JSONResponse(['error' => 'Nicht gefunden.'], Http::STATUS_NOT_FOUND); } if (!$this->access->canManageStatus($existing->employeeUid(), $existing->status()) || !$this->access->canManageStatus($employeeUid, $status)) return $this->denied(); return $this->save($id, compact('employeeUid','startDate','endDate','status','note')); }
    #[NoAdminRequired]
    public function delete(int $id): JSONResponse { try { $vacation = $this->vacations->existing($id); if (!$this->access->canManageStatus($vacation->employeeUid(), $vacation->status())) return $this->denied(); $this->vacations->delete($id); return new JSONResponse(['deleted' => true]); } catch (\Throwable) { return new JSONResponse(['error' => 'Der Urlaub konnte nicht gelöscht werden.'], Http::STATUS_BAD_REQUEST); } }
    public function settings(): JSONResponse { return new JSONResponse(['peerApproval' => $this->settingsService->peerApproval()]); }
    public function saveSettings(array $peerApproval): JSONResponse { return new JSONResponse(['peerApproval' => $this->settingsService->savePeerApproval($peerApproval)]); }
    private function save(?int $id, array $payload): JSONResponse { if (!$this->access->canManageStatus($payload['employeeUid'], $payload['status'])) return $this->denied(); try { return new JSONResponse(['id' => $this->vacations->save($payload, $id, $this->access->currentUser()?->getUID() ?? '')]); } catch (VacationConflictException $error) { return new JSONResponse(['error'=>$error->getMessage(),'conflicts'=>$error->conflicts()],Http::STATUS_CONFLICT); } catch (\Throwable) { return new JSONResponse(['error' => 'Der Urlaub ist ungültig.'], Http::STATUS_BAD_REQUEST); } }
    private function denied(): JSONResponse { return new JSONResponse(['error' => 'Keine Berechtigung.'], Http::STATUS_FORBIDDEN); }
}
