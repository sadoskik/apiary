<template>
    <div class="row">
        <div class="col-12">
            <form id="teamEditForm" v-on:submit.prevent="submit">

                <h3>Team Details</h3>

                <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label">Name<span style="color:red">*</span></label>
                    <div class="col-sm-10 col-lg-4">
                        <input v-model="team.name" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.team.name.$error }" id="name" @blur="$v.team.name.$touch()"
                               placeholder="None on record">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="description" class="col-sm-2 col-form-label">Description<span style="color:red">*</span></label>
                    <div class="col-sm-12 col-lg-6">
                        <textarea v-model="team.description" rows="5" class="form-control" id="description"></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="mailing_list_name" class="col-sm-2 col-form-label">Mailing List Name</label>
                    <div class="input-group col-sm-10 col-lg-4">
                        <input v-model="team.mailing_list_name" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.team.mailing_list_name.$error }" id="mailing_list_name"
                               @blur="$v.team.mailing_list_name.$touch()" placeholder="None on record">
                        <div class="input-group-append">
                            <span class="input-group-text">@lists.gatech.edu</span>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="slack_channel_id" class="col-sm-2 col-form-label">
                        <abbr title="Internal Slack Identifier">Slack Channel ID</abbr></label>
                    <div class="input-group col-sm-10 col-lg-4">
                        <input v-model="team.slack_channel_id" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.team.slack_channel_id.$error }" id="slack_channel_id"
                               @blur="$v.team.slack_channel_id.$touch()" placeholder="None on record">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="slack_channel_name" class="col-sm-2 col-form-label">
                        <abbr title="Public-Facing Name">Slack Channel Name</abbr></label>
                    <div class="input-group col-sm-10 col-lg-4">
                        <div class="input-group-prepend">
                            <span class="input-group-text">#</span>
                        </div>
                        <input v-model="team.slack_channel_name" type="text" class="form-control"
                               :class="{ 'is-invalid': $v.team.slack_channel_name.$error }" id="slack_channel_name"
                               @blur="$v.team.slack_channel_name.$touch()" placeholder="None on record">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="team-visible-buttons" class="col-sm-2 col-form-label">
                        <abbr title="Displayed to users">Visible</abbr><span style="color:red">*</span>
                    </label>
                    <div class="col-sm-10 col-lg-2">
                        <custom-radio-buttons
                                v-model="team.visible"
                                :options="yesNoOptions"
                                id="team-visible-buttons"
                                :is-error="$v.team.visible.$error"
                                @input="$v.team.visible.$touch()">
                        </custom-radio-buttons>
                    </div>

                    <label for="team-attendable-buttons" class="col-sm-2 col-form-label">
                        <abbr title="Used for attendance tracking">Attendable</abbr><span style="color:red">*</span>
                    </label>
                    <div class="col-sm-10 col-lg-2">
                        <custom-radio-buttons
                                v-model="team.attendable"
                                :options="yesNoOptions"
                                id="team-attendable-buttons"
                                :is-error="$v.team.attendable.$error"
                                @input="$v.team.attendable.$touch()">
                        </custom-radio-buttons>
                    </div>

                    <label for="team-self-serviceable-buttons" class="col-sm-2 col-form-label">
                        <abbr title="Users can join/leave via self-service">Self-Serviceable</abbr><span style="color:red">*</span>
                    </label>
                    <div class="col-sm-10 col-lg-2">
                        <custom-radio-buttons
                                v-model="team.self_serviceable"
                                :options="yesNoOptions"
                                id="team-self-serviceable-buttons"
                                :is-error="$v.team.self_serviceable.$error"
                                @input="$v.team.self_serviceable.$touch()">
                        </custom-radio-buttons>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a class="btn btn-secondary" href="/admin/teams">Cancel</a>
                    <button type="button" class="btn btn-danger" @click="deletePrompt">Delete</button>
                    <em><span v-bind:class="{ 'text-danger': hasError}"> {{feedback}} </span></em>
                </div>

            </form>

            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" id="rsvp-tab" data-toggle="tab" href="#rsvps">Members</a>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane show active" id="members">
                    <br/>
                    <team-invite-modal
                            id="teamInviteModal"
                            :teamId="this.teamId">
                    </team-invite-modal>
                    <button type="button" class="btn btn-secondary btn-above-table" data-toggle="modal" data-target="#teamInviteModal">Add Members</button>
                    <datatable id="team-members-table"
                               :data-object="team.members"
                               :columns="memberTableConfig">
                    </datatable>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { required, numeric, alphaNum } from 'vuelidate/lib/validators';

export default {
  name: 'teamEditForm',
  props: ['teamId'],
  mounted() {
    this.dataUrl = this.baseUrl + this.teamId;
    this.loadMembers();

    //Refresh member list on modal close
    $('#teamInviteModal').on('hidden.bs.modal', this.loadMembers);

    //Add jQuery listener to "Remove" button
    //Yes, this is gross. Feel free to make it less gross.
    let table = $('#team-members-table').DataTable();
    let self = this;
    table.on('draw', function() {
      $('[id^=btn-delete-]').click(function(e) {
        self.removeUserPrompt($(this).data('name'), $(this).data('user-id'));
      });
    });
  },
  data() {
    return {
      team: {},
      feedback: '',
      hasError: false,
      baseUrl: '/api/v1/teams/',
      dataURL: '',
      dateTimeConfig: {
        dateFormat: 'Y-m-d H:i:S',
        enableTime: true,
        altInput: true,
      },
      yesNoOptions: [{ value: '0', text: 'No' }, { value: '1', text: 'Yes' }],
      memberTableConfig: [
        { title: 'Name', data: 'name' },
        { title: 'Username', data: 'uid' },
        { title: 'Join Date', data: 'pivot.created_at.date' },
        {
          title: 'Action',
          data: null,
          render: function(data, type, row) {
            return (
              '<button type="button" id="btn-delete-' +
              data.id +
              '" data-name="' +
              data.name +
              '" data-user-id="' +
              data.id +
              '" class="btn btn-danger btn-sm">Remove</button>'
            );
          },
        },
      ],
    };
  },
  validations: {
    team: {
      name: { required },
      description: { required },
      visible: { required },
      attendable: { required },
      self_serviceable: { required },
      mailing_list_name: {},
      slack_channel_id: { alphaNum },
      slack_channel_name: { alphaNum },
    },
  },
  methods: {
    submit() {
      if (this.$v.$invalid) {
        this.$v.$touch();
        return;
      }

      //Unset Members from Team
      delete this.team.members;

      axios
        .put(this.dataUrl, this.team)
        .then(response => {
          this.hasError = false;
          this.feedback = 'Saved!';
          console.log('success');
          var newHREF = '/admin/teams/' + response.data.team.slug;
          if (!window.location.href.endsWith(newHREF)) window.location.href = newHREF;
        })
        .catch(response => {
          this.hasError = true;
          this.feedback = '';
          console.log(response);
          Swal.fire('Error', 'Unable to save data. Check your internet connection or try refreshing the page.', 'error');
        });
    },
    loadMembers() {
      axios
        .get(this.dataUrl, {
          params: {
            include: 'members',
          },
        })
        .then(response => {
          this.team = response.data.team;
        })
        .catch(response => {
          console.log(response);
          Swal.fire(
            'Connection Error',
            'Unable to load data. Check your internet connection or try refreshing the page.',
            'error'
          );
        });
    },
    deletePrompt() {
      let self = this;
      Swal.fire({
        title: 'Are you sure?',
        text: 'Once deleted, you will not be able to recover this team!',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        focusCancel: true,
        confirmButtonColor: '#dc3545',
      }).then(result => {
        if (result.value) {
          self.deleteTeam();
        }
      });
    },
    deleteTeam() {
      axios
        .delete(this.dataUrl)
        .then(response => {
          this.hasError = false;
          Swal.fire({
            title: 'Deleted!',
            text: 'The team has been deleted.',
            type: 'success',
            timer: 3000,
          }).then(result => {
            if (result.value) {
              window.location.href = '/admin/teams';
            }
          });
        })
        .catch(error => {
          this.hasError = true;
          this.feedback = '';
          if (error.response.status == 403) {
            Swal.fire({
              title: 'Whoops!',
              text: "You don't have permission to perform that action.",
              type: 'error',
            });
          } else {
            Swal.fire(
              'Error',
              'Unable to process data. Check your internet connection or try refreshing the page.',
              'error'
            );
          }
        });
    },
    removeUserPrompt: function(name, user_id) {
      let self = this;
      Swal.fire({
        title: 'Are you sure?',
        text: 'Once removed, you must manually re-add this user (' + name + ') to the team to re-join.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove them!',
        focusCancel: true,
        confirmButtonColor: '#dc3545',
      }).then(result => {
        if (result.value) {
          self.removeUser(name, user_id);
        }
      });
    },
    removeUser(name, user_id) {
      let membersUrl = this.baseUrl + this.teamId + '/members';
      let data = {
        user_id: user_id,
        action: 'leave',
      };
      console.log(data);

      axios
        .post(membersUrl, data)
        .then(response => {
          this.hasError = false;
          this.loadMembers();
          Swal.fire({
            title: 'Success!',
            text: 'Removed ' + name + ' from ' + this.team.name + '.',
            type: 'success',
            timer: '1500',
          });
        })
        .catch(error => {
          console.log('error: ' + error);
          this.hasError = true;
          if (error.response.status == 403) {
            Swal.fire({
              title: 'Whoops!',
              text: "You don't have permission to perform that action.",
              type: 'error',
            });
          } else {
            Swal.fire(
              'Error',
              'Unable to process data. Check your internet connection or try refreshing the page.',
              'error'
            );
          }
        });
    },
  },
};
</script>
