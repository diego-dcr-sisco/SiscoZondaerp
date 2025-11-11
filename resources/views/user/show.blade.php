@extends('layouts.app')
@section('content')
    @php
        function isPDF($filePath)
        {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            return $extension == 'pdf' || $extension == 'PDF';
        }
    @endphp

    <style>
        .sidebar {
            color: white;
            text-decoration: none
        }

        .sidebar:hover {
            background-color: #e9ecef;
            color: #212529;
        }
    </style>

    <div class="row w-100 justify-content-between m-0 h-100">
        <div class="col-1 m-0" style="background-color: #343a40;">
            <div class="row">
                <a href="{{ Route('user.show', ['id' => $user->id, 'section' => 1]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Información personal
                </a>
                <a href="{{ Route('user.show', ['id' => $user->id, 'section' => 2]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Información laboral
                </a>
                <a href="{{ Route('user.show', ['id' => $user->id, 'section' => 3]) }}"
                    class="sidebar col-12 p-2 text-center">
                    Contratos
                </a>
            </div>
        </div>

        <div class="col-11">
            <div class="row p-3 border-bottom">
                <a href="javascript:history.back()" class="col-auto btn-primary p-0 fs-3"><i
                        class="bi bi-arrow-left m-3"></i></a>
                <h1 class="col-auto fs-2 m-0">
                    {{ __('user.title.show') }} [<span class="fw-bold">{{ $user->name }}</span>]
                </h1>
            </div>
            <div class="row p-5 pt-3">
                @switch($type)
                    @case(1)
                        <div class="col-12">
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.name') }}:</span>
                                <span class="col fw-normal">{{ $user->name ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.email') }}:</span>
                                <span class="col fw-normal">{{ $user->email ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.phone') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->phone ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.company_phone') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->company_phone ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.birthdate') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->birthdate ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.address') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->address ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.colony') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->colony ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.zip_code') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->zip_code ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.city') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->city ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.state') }}:</span>
                                @if ($user->roleData->state)
                                    @foreach ($states as $state)
                                        @if ($state['key'] == $user->roleData->state)
                                            <span class="col fw-normal">{{ $state['name'] }}</span>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="col fw-normal">{{ 'S/A' }}</span>
                                @endif
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.country') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->country ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.clabe') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->clabe ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.curp') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->curp ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.rfc') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->rfc ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.nss') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->nss ?? 'S/A' }}</span>
                            </div>
                        </div>
                    @break

                    @case(2)
                        <div class="col-12">
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.company') }}:</span>
                                @foreach ($companies as $company)
                                    @if ($company->id == $user->roleData->company_id)
                                        <span class="col fw-normal">{{ $company->name ?? 'S/A' }}</span>
                                    @endif
                                @endforeach
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.hiredate') }}:</span>
                                <span class="col fw-normal">{{ $user->roleData->hiredate ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.role') }}:</span>
                                @foreach ($roles as $role)
                                    @if ($role->id == $user->role_id)
                                        <span class="col fw-normal">{{ $role->name ?? 'S/A' }}</span>
                                    @endif
                                @endforeach
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.department') }}:</span>
                                @foreach ($work_departments as $work_department)
                                    @if ($work_department->id == $user->work_department_id)
                                        <span class="col fw-normal">{{ $work_department->name ?? 'S/A' }}</span>
                                    @endif
                                @endforeach
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.status') }}:</span>
                                <span class="col fw-normal">{{ $status->name ?? 'S/A' }}</span>
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.assigned_branch') }}:</span>
                                @foreach ($branches as $branch)
                                    @if ($branch->id == $user->roleData->branch_id)
                                        <span class="col fw-normal">{{ $branch->name ?? 'S/A' }}</span>
                                    @endif
                                @endforeach
                            </div>
                            <div class="row">
                                <span class="col fw-bold">{{ __('user.data.salary') }}:</span>
                                <span class="col fw-normal">${{ $user->roleData->salary ?? 'S/A' }}</span>
                            </div>
                        </div>
                    @break

                    @case(3)
                        <div class="col-12">
                            @if (!$user->contracts->isEmpty())
                                @foreach ($user->contracts as $contract)
                                    <div class="row">
                                        <span class="col fw-bold">{{ __('user.data.contract_type') }}:</span>
                                        <span
                                            class="col fw-normal">{{ !empty($contract->type->name) ? $contract->type->name : '' ?? 'S/A' }}</span>
                                    </div>
                                    <div class="row">
                                        <span class="col fw-bold">{{ __('user.data.startdate') }}:</span>
                                        <span
                                            class="col fw-normal">{{ !empty($contract->contract_startdate) ? $contract->contract_startdate : '' ?? 'S/A' }}</span>
                                    </div>
                                    <div class="row">
                                        <span class="col fw-bold">{{ __('user.data.enddate') }}: </span>
                                        <span
                                            class="col fw-normal">{{ !empty($contract->contract_enddate) ? $contract->contract_enddate : 'S/A' ?? 'S/A' }}</span>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-danger">No se encontraron contratos</span>
                            @endif
                        </div>
                    @break

                    @default
                @endswitch
            </div>
        </div>
    </div>
@endsection
