<div class="col-lg-12">
    <a class="modalLink primary-btn small fix-gr-bg" data-modal-size="modal-lg" title="@lang('academics.class') {{@$assignedClass->globalClassName->class_name .'('.$assignedClass->globalSectionName->section_name .')' }}  @lang('study.study_material')" href="{{url('global-upload-content-modal?global_class_id='.$assignedClass->class_id.'&'.'global_section_id='.$assignedClass->section_id)}}">@lang('study.study_material') - ({{$globalStudyMat}})</a>
</div>